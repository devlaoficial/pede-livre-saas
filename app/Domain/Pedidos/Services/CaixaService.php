<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Services;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Operacao\Models\CaixaMovimento;
use App\Domain\Operacao\Models\CaixaSessao;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CaixaService
{
    public function abrirSessao(int $lojaId, Usuario $operador, int $valorInicialCentavos): CaixaSessao
    {
        $sessaoAberta = CaixaSessao::where('loja_id', $lojaId)
            ->whereNull('fechado_em')
            ->first();

        if ($sessaoAberta !== null) {
            throw new DomainException('Já existe uma sessão de caixa aberta para esta loja.');
        }

        return CaixaSessao::create([
            'loja_id' => $lojaId,
            'usuario_id' => $operador->id,
            'aberto_em' => now(),
            'valor_inicial_centavos' => $valorInicialCentavos,
        ]);
    }

    public function registrarMovimento(
        CaixaSessao $sessao,
        string $tipo,
        int $valorCentavos,
        ?string $observacao = null
    ): CaixaMovimento {
        if ($sessao->fechado_em !== null) {
            throw new DomainException('Não é possível lançar movimentos em um caixa já fechado.');
        }

        if (!in_array($tipo, ['venda', 'sangria', 'suprimento'], true)) {
            throw new DomainException("Tipo de movimento de caixa inválido: {$tipo}");
        }

        if ($valorCentavos <= 0) {
            throw new DomainException('O valor do movimento deve ser maior que zero.');
        }

        return CaixaMovimento::create([
            'loja_id' => $sessao->loja_id,
            'sessao_id' => $sessao->id,
            'tipo' => $tipo,
            'valor_centavos' => $valorCentavos,
            'observacao' => $observacao,
        ]);
    }

    /**
     * Closes cash register session and calculates exact difference in centavos:
     * Expected = valor_inicial + vendas + suprimentos - sangrias
     * Diferenca = valor_final_informado - Expected
     */
    public function fecharSessao(CaixaSessao $sessao, int $valorFinalInformadoCentavos): CaixaSessao
    {
        if ($sessao->fechado_em !== null) {
            throw new DomainException('Esta sessão de caixa já foi fechada.');
        }

        return DB::transaction(function () use ($sessao, $valorFinalInformadoCentavos): CaixaSessao {
            $vendasDinheiroPedidos = (int) \App\Domain\Pedidos\Models\Pedido::where('loja_id', $sessao->loja_id)
                ->where('forma_pagamento', 'dinheiro')
                ->where('status', '!=', 'cancelado')
                ->where('created_at', '>=', $sessao->aberto_em)
                ->sum('total');

            $vendasMovimentos = (int) CaixaMovimento::where('sessao_id', $sessao->id)
                ->where('tipo', 'venda')
                ->sum('valor_centavos');

            $vendas = max($vendasDinheiroPedidos, $vendasMovimentos);

            $suprimentos = (int) CaixaMovimento::where('sessao_id', $sessao->id)
                ->where('tipo', 'suprimento')
                ->sum('valor_centavos');

            $sangrias = (int) CaixaMovimento::where('sessao_id', $sessao->id)
                ->where('tipo', 'sangria')
                ->sum('valor_centavos');

            $saldoEsperadoCentavos = $sessao->valor_inicial_centavos + $vendas + $suprimentos - $sangrias;
            $diferencaCentavos = $valorFinalInformadoCentavos - $saldoEsperadoCentavos;

            $sessao->update([
                'fechado_em' => now(),
                'valor_final_centavos' => $valorFinalInformadoCentavos,
                'diferenca_centavos' => $diferencaCentavos,
            ]);

            return $sessao;
        });
    }
}
