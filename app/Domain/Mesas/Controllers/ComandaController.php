<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Controllers;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Mesas\Models\Comanda;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Mesas\Services\ComandaService;
use App\Domain\Pedidos\Models\PedidoItem;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ComandaController
{
    public function __construct(
        private readonly ComandaService $comandaService,
        private readonly LojaContext $context
    ) {}

    public function abrir(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if ($user->isCozinha() || $user->isEntregador()) {
            return response()->json(['error' => 'FORBIDDEN'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'mesa_id' => ['required', 'integer'],
            'taxa_servico_pct' => ['nullable', 'integer', 'between:0,30'],
        ]);

        $loja = $this->context->getLoja();
        $mesa = Mesa::where('loja_id', $loja->id)->findOrFail($request->integer('mesa_id'));

        try {
            $comanda = $this->comandaService->abrirComanda(
                $mesa,
                null,
                $request->integer('taxa_servico_pct', 10)
            );

            return response()->json([
                'message' => 'Comanda aberta com sucesso.',
                'comanda' => $comanda,
            ], Response::HTTP_CREATED);
        } catch (DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function dividir(Request $request, int $id): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if ($user->isCozinha()) {
            return response()->json(['error' => 'FORBIDDEN'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'pessoas' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $loja = $this->context->getLoja();
        $comanda = Comanda::where('loja_id', $loja->id)->findOrFail($id);

        $divisao = $this->comandaService->dividirConta($comanda, $request->integer('pessoas'));

        return response()->json($divisao);
    }

    public function transferirItem(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if ($user->isCozinha() || $user->isEntregador()) {
            return response()->json(['error' => 'FORBIDDEN'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'pedido_item_id' => ['required', 'integer'],
            'comanda_destino_id' => ['required', 'integer'],
        ]);

        $loja = $this->context->getLoja();
        $comandaDestino = Comanda::where('loja_id', $loja->id)->findOrFail($request->integer('comanda_destino_id'));
        $item = PedidoItem::whereHas('pedido', fn ($q) => $q->where('loja_id', $loja->id))
            ->findOrFail($request->integer('pedido_item_id'));

        try {
            $this->comandaService->transferirItem($item, $comandaDestino, $user, (string) $request->ip());

            return response()->json([
                'message' => 'Item transferido entre comandas com registro de auditoria.',
            ]);
        } catch (DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function fechar(Request $request, int $id): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if (!$user->isCaixa()) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Apenas operadores de caixa, gerentes ou donos podem fechar comandas.',
            ], Response::HTTP_FORBIDDEN);
        }

        $loja = $this->context->getLoja();
        $comanda = Comanda::where('loja_id', $loja->id)->findOrFail($id);

        $fechada = $this->comandaService->fecharComanda($comanda);

        return response()->json([
            'message' => 'Comanda encerrada e mesa liberada.',
            'comanda' => $fechada,
        ]);
    }
}
