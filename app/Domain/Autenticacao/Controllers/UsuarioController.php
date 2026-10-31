<?php

declare(strict_types=1);

namespace App\Domain\Autenticacao\Controllers;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Autenticacao\Requests\CriarUsuarioRequest;
use App\Domain\Autenticacao\Requests\AtualizarUsuarioRequest;
use App\Domain\Autenticacao\Resources\UsuarioResource;
use App\Support\Security\BlindIndexService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

final class UsuarioController
{
    public function __construct(
        private readonly BlindIndexService $blindIndex,
        private readonly LojaContext $context
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Usuario::class);

        $usuarios = Usuario::orderBy('nome')->get();
        return UsuarioResource::collection($usuarios);
    }

    public function store(CriarUsuarioRequest $request): JsonResponse
    {
        Gate::authorize('create', Usuario::class);

        $loja = $this->context->getLoja();
        $limiteUsuarios = $loja?->plano?->limite_usuarios ?? 2;
        $totalUsuariosAtivos = Usuario::where('ativo', true)->count();

        if ($totalUsuariosAtivos >= $limiteUsuarios) {
            return response()->json([
                'error' => 'LIMITE_PLANO_EXCEDIDO',
                'message' => "Seu plano permite no máximo {$limiteUsuarios} usuários cadastrados. Faça upgrade do plano.",
            ], Response::HTTP_FORBIDDEN);
        }

        $email = $request->string('email')->toString();
        $emailBidx = $this->blindIndex->generateForEmail($email);

        $usuario = Usuario::create([
            'nome' => $request->string('nome')->toString(),
            'email' => $email,
            'email_bidx' => $emailBidx,
            'telefone' => $request->string('telefone')->toString() ?: null,
            'senha_hash' => Hash::make($request->string('senha')->toString()),
            'papel' => $request->string('papel')->toString(),
            'ativo' => true,
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso.',
            'usuario' => new UsuarioResource($usuario),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        Gate::authorize('view', $usuario);

        return response()->json(new UsuarioResource($usuario));
    }

    public function update(AtualizarUsuarioRequest $request, int $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        Gate::authorize('update', $usuario);

        $data = $request->validated();

        if (isset($data['email'])) {
            $data['email_bidx'] = $this->blindIndex->generateForEmail($data['email']);
        }

        if (isset($data['senha']) && !empty($data['senha'])) {
            $data['senha_hash'] = Hash::make($data['senha']);
            unset($data['senha']);
        }

        $usuario->update($data);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso.',
            'usuario' => new UsuarioResource($usuario),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        Gate::authorize('delete', $usuario);

        $usuario->delete();

        return response()->json(['message' => 'Usuário removido com sucesso.']);
    }
}
