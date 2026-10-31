<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Requests\AtualizarLojaRequest;
use App\Domain\Assinaturas\Resources\LojaResource;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class LojaController
{
    public function __construct(
        private readonly LojaContext $context
    ) {}

    public function show(): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('view', $loja);

        return response()->json(new LojaResource($loja));
    }

    public function update(AtualizarLojaRequest $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $loja->update($request->validated());

        return response()->json([
            'message' => 'Configurações da loja atualizadas com sucesso.',
            'loja' => new LojaResource($loja),
        ]);
    }
}
