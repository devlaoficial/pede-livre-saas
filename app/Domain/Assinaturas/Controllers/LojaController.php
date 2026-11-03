<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Controllers;

use App\Domain\Assinaturas\Requests\AtualizarLojaRequest;
use App\Domain\Assinaturas\Resources\LojaResource;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Support\Security\SafeImageUploadService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

final class LojaController
{
    public function __construct(
        private readonly LojaContext $context,
        private readonly SafeImageUploadService $uploadService,
        private readonly CardapioCacheService $cacheService
    ) {}

    public function show(): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('view', $loja);

        return response()->json(new LojaResource($loja->loadMissing('plano')));
    }

    public function update(AtualizarLojaRequest $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $validated = $request->validated();

        if (isset($validated['config_json']) && is_array($validated['config_json'])) {
            $configAtual = is_array($loja->config_json) ? $loja->config_json : [];
            $validated['config_json'] = array_replace_recursive($configAtual, $validated['config_json']);
        }

        if (isset($validated['uf']) && is_string($validated['uf'])) {
            $validated['uf'] = strtoupper($validated['uf']);
        }

        $loja->update($validated);
        $this->cacheService->invalidar($loja->id);

        return response()->json([
            'message' => 'Configurações da loja atualizadas com sucesso.',
            'loja' => new LojaResource($loja->fresh('plano')),
        ]);
    }

    /**
     * POST /api/loja/imagem
     * Uploads store logo or cover banner (capa) converting to WebP.
     */
    public function uploadImagem(Request $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $request->validate([
            'tipo' => ['required', 'string', 'in:logo,capa'],
            'imagem' => ['required', 'file', 'max:4096', 'mimes:jpeg,png,webp,jpg'],
        ]);

        $tipo = $request->string('tipo')->toString();
        /** @var UploadedFile $file */
        $file = $request->file('imagem');

        $result = $this->uploadService->uploadStoreAsset($file, $loja->id, $tipo);

        $loja->update([
            $tipo => $result['url'],
        ]);

        $this->cacheService->invalidar($loja->id);

        return response()->json([
            'message' => ($tipo === 'capa' ? 'Foto de capa' : 'Logotipo') . ' enviado e atualizado com sucesso!',
            'tipo' => $tipo,
            'imagem_url' => $result['url'],
            'loja' => new LojaResource($loja->fresh('plano')),
        ]);
    }
}
