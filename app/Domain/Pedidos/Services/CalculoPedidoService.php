<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Catalogo\Models\GrupoAdicional;
use App\Domain\Catalogo\Models\OpcaoAdicional;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Models\ProdutoVariacao;
use App\Domain\Catalogo\Services\PrecificacaoPizzaService;
use App\Domain\Entregas\Services\CalculadoraFreteService;
use App\Domain\Pedidos\Models\Cupom;
use App\Domain\Pedidos\Models\Pedido;
use DomainException;
use InvalidArgumentException;

final class CalculoPedidoService
{
    public function __construct(
        private readonly CalculadoraFreteService $calculadoraFrete,
        private readonly PrecificacaoPizzaService $precificacaoPizza
    ) {}

    /**
     * 100% Server-Side Order Calculation.
     * Ignores every client price, recalculating products, addons, coupon discounts, fees, and totals in integer centavos.
     *
     * @param Loja $loja
     * @param array<string, mixed> $dados
     * @return array{
     *     subtotal_centavos: int,
     *     taxa_entrega_centavos: int,
     *     desconto_centavos: int,
     *     desconto_cashback_centavos: int,
     *     taxa_servico_centavos: int,
     *     total_centavos: int,
     *     cupom: ?Cupom,
     *     itens_processados: array<int, array<string, mixed>>,
     *     frete_info: ?array<string, mixed>
     * }
     */
    public function calcular(Loja $loja, array $dados): array
    {
        $canal = (string) ($dados['canal'] ?? 'delivery');
        $itensInput = (array) ($dados['itens'] ?? []);

        if (empty($itensInput)) {
            throw new InvalidArgumentException('O pedido deve conter ao menos 1 item.');
        }

        $subtotalCentavos = 0;
        $itensProcessados = [];

        // 1. Calculate each item from Database (ignoring client prices)
        foreach ($itensInput as $index => $itemRaw) {
            $produtoId = (int) ($itemRaw['produto_id'] ?? 0);
            $qtd = max(1, (int) ($itemRaw['qtd'] ?? 1));

            /** @var Produto|null $produto */
            $produto = Produto::where('loja_id', $loja->id)
                ->where('id', $produtoId)
                ->with(['gruposAdicionais.opcoes', 'variacoes'])
                ->first();

            if ($produto === null || !$produto->ativo) {
                throw new DomainException("Produto #{$produtoId} indisponível ou inexistente.");
            }

            if ($produto->esgotado) {
                throw new DomainException("O produto '{$produto->nome}' está esgotado no momento.");
            }

            // Check channel availability
            if (!in_array($canal, $produto->canais ?? [], true)) {
                throw new DomainException("O produto '{$produto->nome}' não está disponível para o canal {$canal}.");
            }

            // Determine unit base price (variation or standard promo/base price)
            $variacaoId = isset($itemRaw['variacao_id']) ? (int) $itemRaw['variacao_id'] : null;
            $variacaoSnapshot = null;
            $precoBaseItem = $produto->preco_efetivo_centavos;

            if ($variacaoId !== null) {
                /** @var ProdutoVariacao|null $variacao */
                $variacao = $produto->variacoes->firstWhere('id', $variacaoId);
                if ($variacao === null) {
                    throw new DomainException("Variação selecionada inválida para o produto '{$produto->nome}'.");
                }
                $precoBaseItem = $variacao->preco_centavos;
                $variacaoSnapshot = $variacao->nome;
            }

            // Validate and Calculate Addon Groups & Multi-flavor Pizzas
            $adicionaisInput = (array) ($itemRaw['adicionais'] ?? []);
            $adicionaisProcessados = [];
            $adicionaisTotalUnit = 0;

            foreach ($produto->gruposAdicionais as $grupo) {
                $opcoesEscolhidasDoGrupo = array_filter(
                    $adicionaisInput,
                    static fn (array $opt): bool => isset($opt['grupo_id'])
                        ? (int) $opt['grupo_id'] === $grupo->id
                        : $grupo->opcoes->contains('id', (int) ($opt['opcao_id'] ?? 0))
                );

                $totalQtdGrupo = count($opcoesEscolhidasDoGrupo);

                if ($grupo->obrigatorio && $totalQtdGrupo < $grupo->minimo) {
                    throw new DomainException("O grupo '{$grupo->nome}' do item '{$produto->nome}' é obrigatório (mínimo: {$grupo->minimo}).");
                }

                if ($totalQtdGrupo > $grupo->maximo) {
                    throw new DomainException("O grupo '{$grupo->nome}' permite no máximo {$grupo->maximo} opções.");
                }

                if ($grupo->isMeioAMeio()) {
                    // Pizza Half-and-Half Rule calculation
                    $saboresModelos = [];
                    foreach ($opcoesEscolhidasDoGrupo as $optRaw) {
                        $opcaoModel = $grupo->opcoes->firstWhere('id', (int) ($optRaw['opcao_id'] ?? 0));
                        if ($opcaoModel !== null) {
                            $saboresModelos[] = $opcaoModel;
                            $adicionaisProcessados[] = [
                                'grupo_id' => $grupo->id,
                                'grupo_nome' => $grupo->nome,
                                'opcao_id' => $opcaoModel->id,
                                'nome' => $opcaoModel->nome,
                                'preco_centavos' => $opcaoModel->preco_centavos,
                            ];
                        }
                    }

                    $precoFinalPizza = $this->precificacaoPizza->calcularPrecoPizza($loja, $precoBaseItem, $saboresModelos);
                    $precoBaseItem = $precoFinalPizza; // Base price absorbs pizza flavor difference
                } else {
                    // Standard Addons
                    foreach ($opcoesEscolhidasDoGrupo as $optRaw) {
                        $opcaoId = (int) ($optRaw['opcao_id'] ?? 0);
                        /** @var OpcaoAdicional|null $opcaoModel */
                        $opcaoModel = $grupo->opcoes->firstWhere('id', $opcaoId);

                        if ($opcaoModel === null || !$opcaoModel->ativo || $opcaoModel->esgotado) {
                            throw new DomainException("A opção de adicional selecionada no grupo '{$grupo->nome}' está esgotada.");
                        }

                        $adicionaisTotalUnit += $opcaoModel->preco_centavos;
                        $adicionaisProcessados[] = [
                            'grupo_id' => $grupo->id,
                            'grupo_nome' => $grupo->nome,
                            'opcao_id' => $opcaoModel->id,
                            'nome' => $opcaoModel->nome,
                            'preco_centavos' => $opcaoModel->preco_centavos,
                        ];
                    }
                }
            }

            $precoTotalUnit = $precoBaseItem + $adicionaisTotalUnit;
            $totalItem = $precoTotalUnit * $qtd;
            $subtotalCentavos += $totalItem;

            $itensProcessados[] = [
                'produto_id' => $produto->id,
                'nome_snapshot' => $produto->nome,
                'variacao_snapshot' => $variacaoSnapshot,
                'qtd' => $qtd,
                'preco_unit' => $precoTotalUnit,
                'adicionais_json' => $adicionaisProcessados,
                'observacao' => $itemRaw['observacao'] ?? null,
                'total' => $totalItem,
            ];
        }

        // 2. Delivery Fee calculation
        $taxaEntregaCentavos = 0;
        $freteInfo = null;

        if ($canal === 'delivery') {
            $endereco = (array) ($dados['endereco'] ?? []);
            $freteInfo = $this->calculadoraFrete->calcular($loja, $endereco, $subtotalCentavos);
            $taxaEntregaCentavos = $freteInfo['taxa_centavos'];
        }

        // 3. Coupon validation & Discount calculation
        $descontoCentavos = 0;
        $cupomModel = null;
        $codigoCupom = isset($dados['cupom_codigo']) ? trim((string) $dados['cupom_codigo']) : null;

        if ($codigoCupom !== null && $codigoCupom !== '') {
            $telefoneBidx = $dados['cliente_telefone_bidx'] ?? null;
            $cupomModel = $this->validarCupom($loja, $codigoCupom, $subtotalCentavos, $canal, $telefoneBidx);

            if ($cupomModel->tipo === 'percentual') {
                $descontoCentavos = (int) round(($subtotalCentavos * $cupomModel->valor) / 100);
            } elseif ($cupomModel->tipo === 'valor') {
                $descontoCentavos = min($subtotalCentavos, $cupomModel->valor);
            } elseif ($cupomModel->tipo === 'frete_gratis') {
                $descontoCentavos = $taxaEntregaCentavos;
                $taxaEntregaCentavos = 0;
            }
        }

        // 4. Cashback discount validation (Premium feature)
        $descontoCashbackCentavos = 0;
        $cashbackResgate = (int) ($dados['cashback_resgate_centavos'] ?? 0);
        if ($cashbackResgate > 0 && $loja->temRecurso('crm_cashback')) {
            $saldoCliente = (int) ($dados['cliente_saldo_cashback_centavos'] ?? 0);
            $maximoResgatePermitido = (int) round($subtotalCentavos * 0.5); // Max 50% discount via cashback

            $descontoCashbackCentavos = min($cashbackResgate, $saldoCliente, $maximoResgatePermitido);
        }

        // 5. Table Service fee (taxa_servico_pct) if canal === 'mesa'
        $taxaServicoCentavos = 0;
        if ($canal === 'mesa') {
            $pct = (int) ($loja->config_json['taxa_servico_pct'] ?? 10);
            $taxaServicoCentavos = (int) round(($subtotalCentavos * $pct) / 100);
        }

        // 6. Final integer centavos total
        $totalCentavos = max(0, $subtotalCentavos - $descontoCentavos - $descontoCashbackCentavos + $taxaEntregaCentavos + $taxaServicoCentavos);

        return [
            'subtotal_centavos' => $subtotalCentavos,
            'taxa_entrega_centavos' => $taxaEntregaCentavos,
            'desconto_centavos' => $descontoCentavos,
            'desconto_cashback_centavos' => $descontoCashbackCentavos,
            'taxa_servico_centavos' => $taxaServicoCentavos,
            'total_centavos' => $totalCentavos,
            'cupom' => $cupomModel,
            'itens_processados' => $itensProcessados,
            'frete_info' => $freteInfo,
        ];
    }

    public function validarCupom(
        Loja $loja,
        string $codigo,
        int $subtotalCentavos,
        string $canal,
        ?string $clienteTelefoneBidx = null
    ): Cupom {
        /** @var Cupom|null $cupom */
        $cupom = Cupom::where('loja_id', $loja->id)
            ->where('codigo', strtoupper($codigo))
            ->first();

        if ($cupom === null || !$cupom->ativo) {
            throw new DomainException("Cupom '{$codigo}' inválido ou desativado.");
        }

        if ($cupom->validade !== null && $cupom->validade->isPast()) {
            throw new DomainException("O cupom '{$codigo}' expirou.");
        }

        if ($cupom->limite_uso_total !== null && $cupom->usos >= $cupom->limite_uso_total) {
            throw new DomainException("O cupom '{$codigo}' atingiu o limite máximo de utilizações.");
        }

        if (!empty($cupom->canais) && !in_array($canal, $cupom->canais, true)) {
            throw new DomainException("O cupom '{$codigo}' não é válido para o canal {$canal}.");
        }

        if ($subtotalCentavos < $cupom->minimo_centavos) {
            $minimoReais = number_format($cupom->minimo_centavos / 100, 2, ',', '.');
            throw new DomainException("O valor mínimo do pedido para utilizar este cupom é de R$ {$minimoReais}.");
        }

        if ($clienteTelefoneBidx !== null && $cupom->limite_por_cliente > 0) {
            $usosCliente = Pedido::where('loja_id', $loja->id)
                ->where('cupom_id', $cupom->id)
                ->whereHas('cliente', fn ($q) => $q->where('telefone_bidx', $clienteTelefoneBidx))
                ->count();

            if ($usosCliente >= $cupom->limite_por_cliente) {
                throw new DomainException("Você já utilizou este cupom o número máximo de vezes permitido ({$cupom->limite_por_cliente}x).");
            }
        }

        return $cupom;
    }
}
