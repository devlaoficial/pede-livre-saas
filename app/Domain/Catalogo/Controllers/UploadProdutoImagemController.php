<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Requests\UploadImagemProdutoRequest;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Support\Security\SafeImageUploadService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class UploadProdutoImagemController
{
    public function __construct(
        private readonly SafeImageUploadService $uploadService,
        private readonly CardapioCacheService $cacheService,
        private readonly LojaContext $context
    ) {}

    public function upload(UploadImagemProdutoRequest $request, int $id): JsonResponse
    {
        $produto = Produto::findOrFail($id);
        Gate::authorize('update', $produto);

        /** @var UploadedFile $file */
        $file = $request->file('imagem');
        $lojaId = (int) $this->context->getLojaId();

        $result = $this->uploadService->uploadProductImage($file, $lojaId);

        if ($produto->imagem !== null && Storage::disk('public')->exists($produto->imagem)) {
            Storage::disk('public')->delete($produto->imagem);
        }

        $produto->update(['imagem' => $result['url']]);
        $this->cacheService->invalidar($lojaId);

        return response()->json([
            'message' => 'Imagem do produto enviada e processada com sucesso.',
            'imagem_path' => $result['path'],
            'imagem_url' => $result['url'],
            'largura' => $result['width'],
            'altura' => $result['height'],
        ]);
    }
}
