<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Operacao\Models\ExcecaoHorario;
use App\Domain\Operacao\Models\Horario;
use Carbon\CarbonImmutable;

final class HorarioFuncionamentoService
{
    /**
     * Evaluates store operating hours across multiple daily shifts and holiday exceptions in store timezone.
     *
     * @param Loja $loja
     * @param string $canal
     * @param CarbonImmutable|null $dataHoraReferencia
     * @return array{aberto_agora: bool, aceita_agendamento: bool, proximo_horario_abertura: ?string, mensagem_status: string}
     */
    public function avaliarStatusLoja(Loja $loja, string $canal = 'delivery', ?CarbonImmutable $dataHoraReferencia = null): array
    {
        $config = is_array($loja->config_json) ? $loja->config_json : [];

        // Check manual store pause toggle ("Loja aberta / fechada")
        if (array_key_exists('aceita_pedidos', $config) && $config['aceita_pedidos'] === false) {
            return [
                'aberto_agora' => false,
                'aceita_agendamento' => false,
                'proximo_horario_abertura' => null,
                'mensagem_status' => 'Fechado temporariamente pelo estabelecimento.',
            ];
        }

        $timezone = $config['timezone'] ?? 'America/Sao_Paulo';
        $now = $dataHoraReferencia ? $dataHoraReferencia->setTimezone($timezone) : CarbonImmutable::now($timezone);

        $hojeData = $now->format('Y-m-d');
        $horaAtual = $now->format('H:i:s');
        $diaSemana = $now->dayOfWeek; // 0 = Sunday, 1 = Monday...

        // 1. Check Date Exception / Holiday Override for today
        $excecao = ExcecaoHorario::where('loja_id', $loja->id)
            ->where('data', $hojeData)
            ->first();

        if ($excecao !== null) {
            if ($excecao->fechado) {
                return [
                    'aberto_agora' => false,
                    'aceita_agendamento' => true,
                    'proximo_horario_abertura' => $this->obterProximaAbertura($loja, $canal, $now),
                    'mensagem_status' => $excecao->descricao ?: 'Fechado por motivo especial/feriado.',
                ];
            }

            if ($excecao->abre !== null && $excecao->fecha !== null) {
                if ($horaAtual >= $excecao->abre && $horaAtual <= $excecao->fecha) {
                    return [
                        'aberto_agora' => true,
                        'aceita_agendamento' => true,
                        'proximo_horario_abertura' => null,
                        'mensagem_status' => 'Aberto hoje em horário especial até ' . substr($excecao->fecha, 0, 5),
                    ];
                }
            }
        }

        // 2. If store has ZERO shifts configured in database across all days, consider open by default
        $possuiAlgumHorario = Horario::where('loja_id', $loja->id)->exists();
        if (!$possuiAlgumHorario) {
            return [
                'aberto_agora' => true,
                'aceita_agendamento' => true,
                'proximo_horario_abertura' => null,
                'mensagem_status' => 'Aberto agora para pedidos',
            ];
        }

        // 3. Check Standard Weekly Shifts for this channel & weekday (fallback to delivery channel if specific channel has no shifts)
        $canalConsulta = Horario::where('loja_id', $loja->id)->where('canal', $canal)->exists() ? $canal : 'delivery';

        $turnosHoje = Horario::where('loja_id', $loja->id)
            ->where('canal', $canalConsulta)
            ->where('dia_semana', $diaSemana)
            ->orderBy('abre')
            ->get();

        foreach ($turnosHoje as $turno) {
            // Standard shift (e.g. 11:00 to 15:00)
            if ($turno->abre <= $turno->fecha) {
                if ($horaAtual >= $turno->abre && $horaAtual <= $turno->fecha) {
                    return [
                        'aberto_agora' => true,
                        'aceita_agendamento' => true,
                        'proximo_horario_abertura' => null,
                        'mensagem_status' => 'Aberto agora até ' . substr($turno->fecha, 0, 5),
                    ];
                }
            } else {
                // Overnight shift (e.g. 18:00 to 02:00)
                if ($horaAtual >= $turno->abre || $horaAtual <= $turno->fecha) {
                    return [
                        'aberto_agora' => true,
                        'aceita_agendamento' => true,
                        'proximo_horario_abertura' => null,
                        'mensagem_status' => 'Aberto agora até ' . substr($turno->fecha, 0, 5),
                    ];
                }
            }
        }

        $proximaAbertura = $this->obterProximaAbertura($loja, $canalConsulta, $now);

        return [
            'aberto_agora' => false,
            'aceita_agendamento' => true,
            'proximo_horario_abertura' => $proximaAbertura,
            'mensagem_status' => $proximaAbertura ? "Fechado no momento. Abre {$proximaAbertura}." : 'Fechado no momento.',
        ];
    }

    private function obterProximaAbertura(Loja $loja, string $canal, CarbonImmutable $now): ?string
    {
        $horaAtual = $now->format('H:i:s');
        $diaSemana = $now->dayOfWeek;

        // Check if there is another shift later today
        $proximoHoje = Horario::where('loja_id', $loja->id)
            ->where('canal', $canal)
            ->where('dia_semana', $diaSemana)
            ->where('abre', '>', $horaAtual)
            ->orderBy('abre')
            ->first();

        if ($proximoHoje !== null) {
            return 'hoje às ' . substr($proximoHoje->abre, 0, 5);
        }

        // Check upcoming days within next 7 days
        for ($i = 1; $i <= 7; $i++) {
            $proxDia = ($diaSemana + $i) % 7;
            $turno = Horario::where('loja_id', $loja->id)
                ->where('canal', $canal)
                ->where('dia_semana', $proxDia)
                ->orderBy('abre')
                ->first();

            if ($turno !== null) {
                $nomeDia = match ($proxDia) {
                    0 => 'domingo',
                    1 => 'segunda-feira',
                    2 => 'terça-feira',
                    3 => 'quarta-feira',
                    4 => 'quinta-feira',
                    5 => 'sexta-feira',
                    6 => 'sábado',
                };
                return "{$nomeDia} às " . substr($turno->abre, 0, 5);
            }
        }

        return null;
    }
}
