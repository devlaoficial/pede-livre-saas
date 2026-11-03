<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Controllers;

use App\Domain\Operacao\Models\Horario;
use App\Domain\Operacao\Requests\SalvarHorarioRequest;
use App\Domain\Operacao\Resources\HorarioResource;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class HorarioController
{
    public function __construct(
        private readonly LojaContext $context
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $canal = $request->string('canal', 'delivery')->toString();
        $horarios = Horario::where('canal', $canal)
            ->orderBy('dia_semana')
            ->orderBy('abre')
            ->get();

        return HorarioResource::collection($horarios);
    }

    /**
     * Bulk save or sync weekly shifts for the store (replaces existing shifts for submitted channels cleanly).
     */
    public function salvarTurnos(SalvarHorarioRequest $request): JsonResponse
    {
        Gate::authorize('manage', Horario::class);

        $turnos = $request->validated('turnos');
        $lojaId = (int) $this->context->getLojaId();

        DB::transaction(function () use ($turnos, $lojaId): void {
            $canaisSubmetidos = array_values(array_unique(array_column($turnos, 'canal')));
            if (!empty($canaisSubmetidos)) {
                Horario::where('loja_id', $lojaId)
                    ->whereIn('canal', $canaisSubmetidos)
                    ->delete();
            }

            foreach ($turnos as $turno) {
                $abre = strlen($turno['abre']) === 5 ? $turno['abre'] . ':00' : $turno['abre'];
                $fecha = strlen($turno['fecha']) === 5 ? $turno['fecha'] . ':00' : $turno['fecha'];

                Horario::create([
                    'loja_id' => $lojaId,
                    'canal' => $turno['canal'],
                    'dia_semana' => (int) $turno['dia_semana'],
                    'abre' => $abre,
                    'fecha' => $fecha,
                ]);
            }
        });

        return response()->json(['message' => 'Grade de horários atualizada com sucesso.']);
    }

    public function destroy(int $id): JsonResponse
    {
        Gate::authorize('manage', Horario::class);

        $horario = Horario::findOrFail($id);
        $horario->delete();

        return response()->json(['message' => 'Turno removido com sucesso.']);
    }
}
