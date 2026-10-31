<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Controllers;

use App\Domain\Mesas\Models\Mesa;
use App\Domain\Mesas\Requests\SalvarMesaRequest;
use App\Domain\Mesas\Resources\MesaAdminResource;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class MesaAdminController
{
    public function __construct(
        private readonly LojaContext $context
    ) {}
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Mesa::class);

        $mesas = Mesa::with(['comandaAtiva', 'loja'])->orderBy('numero')->get();
        return MesaAdminResource::collection($mesas);
    }

    public function store(SalvarMesaRequest $request): JsonResponse
    {
        Gate::authorize('create', Mesa::class);

        $mesa = Mesa::create($request->validated());

        return response()->json([
            'message' => 'Mesa criada com sucesso.',
            'mesa' => new MesaAdminResource($mesa->load('loja')),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $mesa = Mesa::with(['comandaAtiva', 'loja'])->findOrFail($id);
        Gate::authorize('view', $mesa);

        return response()->json(new MesaAdminResource($mesa));
    }

    public function update(SalvarMesaRequest $request, int $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);
        Gate::authorize('update', $mesa);

        $mesa->update($request->validated());

        return response()->json([
            'message' => 'Mesa atualizada com sucesso.',
            'mesa' => new MesaAdminResource($mesa->load('loja')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);
        Gate::authorize('delete', $mesa);

        $mesa->delete();

        return response()->json(['message' => 'Mesa removida com sucesso.']);
    }

    /**
     * Regenerates the cryptographic 32-char QR code token on demand if compromised.
     */
    public function regenerarQr(int $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);
        Gate::authorize('update', $mesa);

        $novoToken = $mesa->regenerarQrToken();

        return response()->json([
            'message' => 'Token QR da mesa regenerado com sucesso.',
            'novo_qr_token' => $novoToken,
            'mesa' => new MesaAdminResource($mesa->load('loja')),
        ]);
    }

    /**
     * Lists active waiter calls for the current store.
     */
    public function chamadosAtivos(): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['chamados' => []]);
        }

        $cacheKey = "garcom_chamados:loja:{$loja->id}";
        $chamados = Cache::get($cacheKey, []);

        return response()->json([
            'chamados' => is_array($chamados) ? array_values($chamados) : [],
        ]);
    }

    /**
     * Clears/acknowledges an active waiter call for a specific table.
     */
    public function atenderChamado(int $id): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja !== null) {
            $cacheKey = "garcom_chamados:loja:{$loja->id}";
            $chamados = Cache::get($cacheKey, []);
            if (is_array($chamados) && isset($chamados[(string) $id])) {
                unset($chamados[(string) $id]);
                Cache::put($cacheKey, $chamados, now()->addMinutes(30));
            }
        }

        return response()->json([
            'message' => 'Chamado da mesa atendido com sucesso.',
            'mesa_id' => $id,
        ]);
    }
}
