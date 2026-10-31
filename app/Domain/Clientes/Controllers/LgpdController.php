<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Controllers;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\EnderecoCliente;
use App\Domain\Pedidos\Models\Pedido;
use App\Support\Security\BlindIndexService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class LgpdController
{
    public function __construct(
        private readonly BlindIndexService $blindIndex,
        private readonly LojaContext $context
    ) {}

    /**
     * GET /api/lgpd/exportar
     * Exports all personal data and order history for a customer (LGPD Art. 18 Portability/Access).
     */
    public function exportar(Request $request): JsonResponse
    {
        $request->validate([
            'telefone' => ['required', 'string'],
        ]);

        $loja = $this->context->getLoja();
        $telefoneBidx = $this->blindIndex->generateForPhone($request->string('telefone')->toString());

        /** @var Cliente|null $cliente */
        $cliente = Cliente::where('loja_id', $loja->id)
            ->where('telefone_bidx', $telefoneBidx)
            ->with(['enderecos', 'pedidos.itens'])
            ->firstOrFail();

        return response()->json([
            'titular' => [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
                'telefone' => $cliente->telefone,
                'email' => $cliente->email,
                'aceita_marketing' => $cliente->aceita_marketing,
                'consentimento_em' => $cliente->consentimento_em?->toIso8601String(),
                'saldo_cashback_centavos' => $cliente->saldo_cashback_centavos,
                'total_pedidos' => $cliente->total_pedidos,
                'total_gasto_centavos' => $cliente->total_gasto,
            ],
            'enderecos' => $cliente->enderecos->map(fn (EnderecoCliente $e) => [
                'apelido' => $e->apelido,
                'cep' => $e->cep,
                'rua' => $e->rua,
                'numero' => $e->numero,
                'complemento' => $e->complemento,
                'bairro' => $e->bairro,
                'cidade' => $e->cidade,
                'uf' => $e->uf,
            ]),
            'pedidos_historico' => $cliente->pedidos->map(fn (Pedido $p) => [
                'codigo_publico' => $p->codigo_publico,
                'data' => $p->created_at?->toIso8601String(),
                'total_centavos' => $p->total,
                'forma_pagamento' => $p->forma_pagamento,
            ]),
            'exportado_em' => now()->toIso8601String(),
        ]);
    }

    /**
     * DELETE /api/lgpd/excluir
     * Anonymizes customer PII (name, phone, email, addresses, order address snapshots)
     * while strictly preserving fiscal order financial totals (subtotal, taxas, total).
     */
    public function anonimizar(Request $request): JsonResponse
    {
        $request->validate([
            'telefone' => ['required', 'string'],
        ]);

        $loja = $this->context->getLoja();
        $telefoneBidx = $this->blindIndex->generateForPhone($request->string('telefone')->toString());

        /** @var Cliente $cliente */
        $cliente = Cliente::where('loja_id', $loja->id)
            ->where('telefone_bidx', $telefoneBidx)
            ->firstOrFail();

        DB::transaction(function () use ($cliente, $loja): void {
            // 1. Delete saved addresses
            EnderecoCliente::where('cliente_id', $cliente->id)->delete();

            // 2. Scrub PII from historical order address snapshots while keeping financial totals intact
            // (per-model update so the 'encrypted:array' cast is applied to endereco_json)
            Pedido::where('loja_id', $loja->id)
                ->where('cliente_id', $cliente->id)
                ->get()
                ->each(function (Pedido $pedido): void {
                    $pedido->update([
                        'endereco_json' => [
                            'rua' => '[ANONIMIZADO LGPD]',
                            'numero' => 'S/N',
                            'bairro' => 'Anonimizado',
                        ],
                    ]);
                });

            // 3. Anonymize customer record with non-reversible random token
            $anonToken = 'ANON_' . Str::random(24);
            $cliente->update([
                'nome' => 'Titular Anonimizado (LGPD)',
                'telefone' => '00000000000',
                'telefone_bidx' => hash_hmac('sha256', $anonToken, (string) config('app.blind_index_key')),
                'email' => null,
                'email_bidx' => null,
                'aceita_marketing' => false,
            ]);
        });

        return response()->json([
            'message' => 'Dados pessoais anonimizados com sucesso conforme a LGPD, mantendo os registros fiscais.',
            'cliente_id' => $cliente->id,
        ]);
    }
}
