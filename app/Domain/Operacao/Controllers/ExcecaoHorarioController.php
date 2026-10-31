<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Controllers;

use App\Domain\Operacao\Models\ExcecaoHorario;
use App\Domain\Operacao\Requests\SalvarExcecaoHorarioRequest;
use App\Domain\Operacao\Resources\ExcecaoHorarioResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class ExcecaoHorarioController
{
    public function index(): AnonymousResourceCollection
    {
        $excecoes = ExcecaoHorario::orderBy('data')->get();
        return ExcecaoHorarioResource::collection($excecoes);
    }

    public function store(SalvarExcecaoHorarioRequest $request): JsonResponse
    {
        Gate::authorize('manage', ExcecaoHorario::class);

        $excecao = ExcecaoHorario::create($request->validated());

        return response()->json([
            'message' => 'Exceção de horário cadastrada com sucesso.',
            'excecao' => new ExcecaoHorarioResource($excecao),
        ], Response::HTTP_CREATED);
    }

    public function destroy(int $id): JsonResponse
    {
        Gate::authorize('manage', ExcecaoHorario::class);

        $excecao = ExcecaoHorario::findOrFail($id);
        $excecao->delete();

        return response()->json(['message' => 'Exceção removida com sucesso.']);
    }
}
