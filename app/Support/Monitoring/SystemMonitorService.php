<?php

declare(strict_types=1);

namespace App\Support\Monitoring;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use ZipArchive;

final class SystemMonitorService
{
    private const CONFIG_FILE = 'system_monitor_config.json';
    private const INCIDENTS_FILE = 'system_incidents_log.json';
    private const BACKUP_DIR = 'backups';

    public function __construct(
        private readonly EvolutionApiService $evolutionApi
    ) {}

    /**
     * Obtém as configurações de monitoramento, alertas (E-mail/WhatsApp) e backup automático.
     *
     * @return array<string, mixed>
     */
    public function obterConfiguracao(): array
    {
        $padrao = [
            'alert_email' => (string) config('services.monitoring.alert_email', env('ADMIN_ALERT_EMAIL', '')),
            'alert_whatsapp' => (string) config('services.monitoring.alert_whatsapp', env('ADMIN_ALERT_WHATSAPP', '')),
            'whatsapp_instance' => (string) config('services.monitoring.whatsapp_instance', env('ADMIN_WHATSAPP_INSTANCE', '')),
            'alerta_queda_ativo' => true,
            'alerta_erros_500_ativo' => true,
            'backup_auto_ativo' => true,
            'backup_enviar_email' => true,
            'backup_enviar_resumo_whatsapp' => true,
            'backup_horario' => '03:00',
            'retencao_backups_qtd' => 15,
        ];

        $path = storage_path('app/' . self::CONFIG_FILE);
        if (File::exists($path)) {
            $salvo = json_decode((string) File::get($path), true);
            if (is_array($salvo)) {
                return array_merge($padrao, $salvo);
            }
        }

        return $padrao;
    }

    /**
     * Salva as configurações de monitoramento e backup.
     *
     * @param array<string, mixed> $novosDados
     * @return array<string, mixed>
     */
    public function salvarConfiguracao(array $novosDados): array
    {
        $atual = $this->obterConfiguracao();
        $atualizado = array_merge($atual, [
            'alert_email' => trim((string) ($novosDados['alert_email'] ?? $atual['alert_email'])),
            'alert_whatsapp' => preg_replace('/\D/', '', (string) ($novosDados['alert_whatsapp'] ?? $atual['alert_whatsapp'])),
            'whatsapp_instance' => trim((string) ($novosDados['whatsapp_instance'] ?? $atual['whatsapp_instance'])),
            'alerta_queda_ativo' => (bool) ($novosDados['alerta_queda_ativo'] ?? $atual['alerta_queda_ativo']),
            'alerta_erros_500_ativo' => (bool) ($novosDados['alerta_erros_500_ativo'] ?? $atual['alerta_erros_500_ativo']),
            'backup_auto_ativo' => (bool) ($novosDados['backup_auto_ativo'] ?? $atual['backup_auto_ativo']),
            'backup_enviar_email' => (bool) ($novosDados['backup_enviar_email'] ?? $atual['backup_enviar_email']),
            'backup_enviar_resumo_whatsapp' => (bool) ($novosDados['backup_enviar_resumo_whatsapp'] ?? $atual['backup_enviar_resumo_whatsapp']),
            'backup_horario' => (string) ($novosDados['backup_horario'] ?? $atual['backup_horario']),
        ]);

        $path = storage_path('app/' . self::CONFIG_FILE);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($atualizado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $atualizado;
    }

    /**
     * Executa diagnóstico completo de saúde dos subsistemas (Banco, Cache, Disco, Evolution API).
     * Se $dispararAlertaSeDegradado = true, envia alerta por E-mail e WhatsApp caso algum componente crítico falhe.
     *
     * @return array<string, mixed>
     */
    public function verificarSaudeSistema(bool $dispararAlertaSeDegradado = false): array
    {
        $checks = [];
        $problemasCriticos = [];

        // 1. Banco de Dados
        $t0 = microtime(true);
        try {
            DB::select('SELECT 1');
            $latenciaDb = round((microtime(true) - $t0) * 1000, 1);
            $checks['database'] = [
                'status' => 'ok',
                'latency_ms' => $latenciaDb,
                'message' => "Conectado ({$latenciaDb} ms)",
            ];
        } catch (Throwable $e) {
            $checks['database'] = [
                'status' => 'down',
                'latency_ms' => null,
                'message' => 'FALHA CRÍTICA NO BANCO DE DADOS: ' . $e->getMessage(),
            ];
            $problemasCriticos[] = 'Banco de Dados inacessível: ' . $e->getMessage();
        }

        // 2. Cache & Sessões
        $t1 = microtime(true);
        try {
            $chaveTeste = 'healthcheck_ping_' . time();
            Cache::put($chaveTeste, 'ok', 10);
            $valorLido = Cache::get($chaveTeste);
            Cache::forget($chaveTeste);
            $latenciaCache = round((microtime(true) - $t1) * 1000, 1);

            if ($valorLido === 'ok') {
                $checks['cache'] = [
                    'status' => 'ok',
                    'latency_ms' => $latenciaCache,
                    'message' => 'Operacional (' . config('cache.default') . " • {$latenciaCache} ms)",
                ];
            } else {
                throw new \RuntimeException('Falha de leitura/escrita no driver de cache.');
            }
        } catch (Throwable $e) {
            $checks['cache'] = [
                'status' => 'down',
                'latency_ms' => null,
                'message' => 'Falha no Cache: ' . $e->getMessage(),
            ];
            $problemasCriticos[] = 'Serviço de Cache falhou: ' . $e->getMessage();
        }

        // 3. Armazenamento & Espaço em Disco
        try {
            $storagePath = storage_path('app');
            File::ensureDirectoryExists($storagePath);
            $livreBytes = @disk_free_space($storagePath) ?: 0;
            $totalBytes = @disk_total_space($storagePath) ?: 1;
            $livreGb = round($livreBytes / (1024 ** 3), 2);
            $pctLivre = round(($livreBytes / max(1, $totalBytes)) * 100, 1);
            $gravavel = is_writable($storagePath);

            if (!$gravavel || $pctLivre < 3.0) {
                $checks['storage'] = [
                    'status' => 'down',
                    'free_gb' => $livreGb,
                    'free_pct' => $pctLivre,
                    'message' => !$gravavel ? 'Diretório storage sem permissão de escrita!' : "Disco crítico ({$livreGb} GB livres / {$pctLivre}%)",
                ];
                $problemasCriticos[] = $checks['storage']['message'];
            } else {
                $checks['storage'] = [
                    'status' => 'ok',
                    'free_gb' => $livreGb,
                    'free_pct' => $pctLivre,
                    'message' => "Saudável ({$livreGb} GB livres • {$pctLivre}%)",
                ];
            }
        } catch (Throwable $e) {
            $checks['storage'] = [
                'status' => 'warning',
                'message' => 'Não foi possível aferir espaço em disco: ' . $e->getMessage(),
            ];
        }

        // 4. Microserviço WhatsApp Evolution API
        $t2 = microtime(true);
        try {
            $evoUrl = rtrim((string) config('services.evolution.url', 'http://127.0.0.1:8085'), '/');
            $evoKey = (string) (config('services.evolution.api_key') ?: 'devla_evolution_secret_2026');
            $resp = Http::withHeaders(['apikey' => $evoKey])
                ->connectTimeout(2)
                ->timeout(3)
                ->get($evoUrl);

            $latenciaEvo = round((microtime(true) - $t2) * 1000, 1);
            $checks['whatsapp_gateway'] = [
                'status' => 'ok',
                'latency_ms' => $latenciaEvo,
                'message' => "Online ({$latenciaEvo} ms)",
            ];
        } catch (Throwable $e) {
            $checks['whatsapp_gateway'] = [
                'status' => 'warning',
                'latency_ms' => null,
                'message' => 'Gateway WhatsApp sem resposta (' . config('services.evolution.url') . ')',
            ];
        }

        $statusGeral = empty($problemasCriticos) ? 'healthy' : 'critical';

        if ($dispararAlertaSeDegradado && !empty($problemasCriticos)) {
            $this->dispararAlertaIncidente(
                '🚨 ALERTA CRÍTICO: Subsistema PedeLivre Indisponível',
                "O monitor automático detectou falha crítica na infraestrutura do PedeLivre OS:\n\n• " . implode("\n• ", $problemasCriticos),
                'healthcheck_critical'
            );
        }

        return [
            'status' => $statusGeral,
            'checked_at' => now()->format('d/m/Y H:i:s'),
            'checks' => $checks,
            'incidentes_recentes' => $this->listarIncidentesRecentes(),
            'backups' => $this->listarBackups(),
            'config' => $this->obterConfiguracao(),
        ];
    }

    /**
     * Captura exceções críticas não tratadas (Erro 500) em qualquer rota do sistema,
     * grava no log de incidentes e alerta o administrador via WhatsApp e E-mail (com controle anti-spam).
     */
    public function reportarExcecaoCritica(Throwable $e, ?Request $request = null): void
    {
        try {
            $classe = get_class($e);
            $ignorar = [
                \Illuminate\Validation\ValidationException::class,
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Auth\Access\AuthorizationException::class,
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
                \Symfony\Component\HttpKernel\Exception\HttpException::class,
                \Illuminate\Session\TokenMismatchException::class,
                \DomainException::class,
            ];

            foreach ($ignorar as $ignorableClass) {
                if (is_a($e, $ignorableClass)) {
                    return;
                }
            }

            $url = $request ? $request->fullUrl() : 'CLI / Background';
            $metodo = $request ? $request->method() : 'CLI';
            $arquivo = basename($e->getFile()) . ':' . $e->getLine();
            $mensagem = $e->getMessage() ?: $classe;

            $incidente = [
                'id' => uniqid('inc_'),
                'data' => now()->format('d/m/Y H:i:s'),
                'tipo' => class_basename($classe),
                'mensagem' => mb_substr($mensagem, 0, 350),
                'arquivo' => $arquivo,
                'url' => "{$metodo} {$url}",
            ];

            $this->registrarIncidenteNoHistorico($incidente);

            $cfg = $this->obterConfiguracao();
            if (empty($cfg['alerta_erros_500_ativo'])) {
                return;
            }

            $chaveDeduplicacao = 'alerta_erro_500_' . md5($classe . ':' . $arquivo . ':' . $mensagem);
            $resumo = "🚨 *ERRO CRÍTICO DETECTADO NO SISTEMA*\n\n"
                . "⚠️ *Erro:* {$incidente['tipo']}\n"
                . "💬 *Detalhe:* {$incidente['mensagem']}\n"
                . "📂 *Local:* {$incidente['arquivo']}\n"
                . "🌐 *Rota:* {$incidente['url']}\n"
                . "🕒 *Horário:* {$incidente['data']}";

            $this->dispararAlertaIncidente(
                "🚨 [PedeLivre OS] Erro Crítico Detectado ({$incidente['tipo']})",
                $resumo,
                $chaveDeduplicacao,
                15 // Cooldown de 15 min por erro idêntico para evitar spam
            );
        } catch (Throwable) {
            // Jamais permitir loop em tratador de exceção
        }
    }

    /**
     * Envia alerta operacional por E-mail e WhatsApp para o administrador do sistema.
     *
     * @return array{email_enviado: bool, whatsapp_enviado: bool}
     */
    public function dispararAlertaIncidente(
        string $assunto,
        string $mensagemTexto,
        ?string $chaveCooldown = null,
        int $minutosCooldown = 15
    ): array {
        if ($chaveCooldown !== null) {
            try {
                if (Cache::has($chaveCooldown)) {
                    return ['email_enviado' => false, 'whatsapp_enviado' => false];
                }
                Cache::put($chaveCooldown, true, now()->addMinutes($minutosCooldown));
            } catch (Throwable) {}
        }

        $cfg = $this->obterConfiguracao();
        $emailDestino = trim((string) ($cfg['alert_email'] ?? ''));
        $whatsappDestino = preg_replace('/\D/', '', (string) ($cfg['alert_whatsapp'] ?? ''));

        $emailEnviado = false;
        $whatsappEnviado = false;

        // 1. Disparo via WhatsApp
        if ($whatsappDestino !== '') {
            try {
                $instancia = $this->resolverInstanciaAlerta($cfg);
                if ($instancia !== '') {
                    $whatsappEnviado = $this->evolutionApi->enviarMensagem($instancia, $whatsappDestino, $mensagemTexto);
                }
            } catch (Throwable $e) {
                Log::warning('[SystemMonitor] Falha ao enviar alerta por WhatsApp: ' . $e->getMessage());
            }
        }

        // 2. Disparo via E-mail
        if ($emailDestino !== '' && filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::raw($mensagemTexto, function ($mail) use ($emailDestino, $assunto): void {
                    $mail->to($emailDestino)->subject($assunto);
                });
                $emailEnviado = true;
            } catch (Throwable $e) {
                Log::warning('[SystemMonitor] Falha ao enviar alerta por E-mail: ' . $e->getMessage());
            }
        }

        return [
            'email_enviado' => $emailEnviado,
            'whatsapp_enviado' => $whatsappEnviado,
        ];
    }

    /**
     * Gera um pacote completo de Backup (.zip) contendo:
     * 1. Dump SQL completo de todas as tabelas (estrutura CREATE TABLE + dados INSERT INTO via PDO nativo)
     * 2. Snapshot JSON estruturado de segurança
     * 3. Manifesto com hash de integridade SHA-256
     * E envia automaticamente para o e-mail configurado do administrador!
     *
     * @return array<string, mixed>
     */
    public function gerarBackupCompleto(bool $enviarPorEmail = true): array
    {
        $diretorioBackups = storage_path('app/' . self::BACKUP_DIR);
        File::ensureDirectoryExists($diretorioBackups);

        $timestamp = now()->format('Y-m-d_H-i-s');
        $nomeArquivoZip = "backup-pedelivre-{$timestamp}.zip";
        $caminhoZip = "{$diretorioBackups}/{$nomeArquivoZip}";

        // 1. Gera Dump SQL completo e portátil via PDO nativo (não depende de binário externo mysqldump)
        $resumoTabelas = [];
        $sqlDump = $this->gerarDumpSqlPortatil($resumoTabelas);

        // 2. Gera Snapshot JSON executivo das entidades críticas
        $snapshot = [
            'gerado_em' => now()->toIso8601String(),
            'app_url' => config('app.url'),
            'resumo_tabelas' => $resumoTabelas,
            'lojas' => Loja::withoutGlobalScopes()->get()->toArray(),
            'pedidos_recentes' => Pedido::withoutGlobalScopes()->latest('id')->limit(500)->get()->toArray(),
        ];

        $manifesto = [
            'sistema' => 'PedeLivre OS SaaS',
            'arquivo' => $nomeArquivoZip,
            'gerado_em' => now()->format('d/m/Y H:i:s'),
            'php_version' => PHP_VERSION,
            'tabelas_exportadas' => count($resumoTabelas),
            'total_registros' => array_sum($resumoTabelas),
            'contagem_por_tabela' => $resumoTabelas,
        ];

        // 3. Compacta no pacote ZIP
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            if ($zip->open($caminhoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFromString("database-dump-{$timestamp}.sql", $sqlDump);
                $zip->addFromString("snapshot-dados-{$timestamp}.json", json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $zip->addFromString('manifesto-backup.json', json_encode($manifesto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $zip->close();
            } else {
                throw new \RuntimeException('Não foi possível criar o arquivo ZIP de backup.');
            }
        } else {
            // Fallback caso extensão zip não esteja habilitada
            $nomeArquivoZip = "backup-pedelivre-{$timestamp}.sql";
            $caminhoZip = "{$diretorioBackups}/{$nomeArquivoZip}";
            File::put($caminhoZip, $sqlDump);
        }

        $tamanhoBytes = File::exists($caminhoZip) ? File::size($caminhoZip) : 0;
        $hashSha256 = File::exists($caminhoZip) ? hash_file('sha256', $caminhoZip) : '';

        // 4. Rotação automática de backups antigos (mantém os últimos N arquivos)
        $this->limparBackupsAntigos((int) ($this->obterConfiguracao()['retencao_backups_qtd'] ?? 15));

        // 5. Envia o Backup por E-mail e avisa no WhatsApp (se configurado)
        $emailEnviado = false;
        $whatsappEnviado = false;
        $cfg = $this->obterConfiguracao();
        $emailDestino = trim((string) ($cfg['alert_email'] ?? ''));
        $whatsappDestino = preg_replace('/\D/', '', (string) ($cfg['alert_whatsapp'] ?? ''));

        $tamanhoFormatado = $this->formatarTamanho($tamanhoBytes);
        $totalRegistros = array_sum($resumoTabelas);
        $qtdTabelas = count($resumoTabelas);

        $textoResumo = "📦 *BACKUP COMPLETO DO SISTEMA CONCLUÍDO*\n\n"
            . "✅ *Arquivo:* {$nomeArquivoZip}\n"
            . "📊 *Tabelas exportadas:* {$qtdTabelas} tabelas ({$totalRegistros} registros)\n"
            . "💾 *Tamanho:* {$tamanhoFormatado}\n"
            . "🔒 *SHA-256:* " . substr((string) $hashSha256, 0, 16) . "...\n"
            . "🕒 *Data/Hora:* " . now()->format('d/m/Y H:i:s');

        if ($enviarPorEmail && $emailDestino !== '' && filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            try {
                $assunto = '📦 [PedeLivre OS] Backup Completo do Sistema — ' . now()->format('d/m/Y H:i');
                $anexarArquivo = $tamanhoBytes > 0 && $tamanhoBytes <= (20 * 1024 * 1024); // Até 20MB anexa direto no e-mail

                Mail::raw(
                    $textoResumo . "\n\n" . ($anexarArquivo
                        ? 'O arquivo completo do backup (.zip com dump SQL e JSON) segue em anexo neste e-mail.'
                        : 'O arquivo está salvo com segurança no painel Super Admin para download imediato.'),
                    function ($mail) use ($emailDestino, $assunto, $caminhoZip, $nomeArquivoZip, $anexarArquivo): void {
                        $mail->to($emailDestino)->subject($assunto);
                        if ($anexarArquivo && File::exists($caminhoZip)) {
                            $mail->attach($caminhoZip, ['as' => $nomeArquivoZip]);
                        }
                    }
                );
                $emailEnviado = true;
            } catch (Throwable $e) {
                Log::warning('[SystemMonitor] Falha ao enviar backup por e-mail: ' . $e->getMessage());
            }
        }

        if ($enviarPorEmail && !empty($cfg['backup_enviar_resumo_whatsapp']) && $whatsappDestino !== '') {
            try {
                $instancia = $this->resolverInstanciaAlerta($cfg);
                if ($instancia !== '') {
                    $msgWpp = $textoResumo . ($emailEnviado ? "\n\n📧 _Uma cópia em anexo foi enviada para {$emailDestino}!_" : '');
                    $whatsappEnviado = $this->evolutionApi->enviarMensagem($instancia, $whatsappDestino, $msgWpp);
                }
            } catch (Throwable) {}
        }

        return [
            'arquivo' => $nomeArquivoZip,
            'caminho' => $caminhoZip,
            'tamanho_bytes' => $tamanhoBytes,
            'tamanho_humano' => $tamanhoFormatado,
            'sha256' => $hashSha256,
            'tabelas' => $qtdTabelas,
            'total_registros' => $totalRegistros,
            'email_enviado' => $emailEnviado,
            'whatsapp_enviado' => $whatsappEnviado,
            'data' => now()->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Lista os arquivos de backup disponíveis em storage/app/backups.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarBackups(): array
    {
        $diretorioBackups = storage_path('app/' . self::BACKUP_DIR);
        if (!File::isDirectory($diretorioBackups)) {
            return [];
        }

        $arquivos = File::files($diretorioBackups);
        $lista = [];

        foreach ($arquivos as $arq) {
            $nome = $arq->getFilename();
            if (!str_ends_with($nome, '.zip') && !str_ends_with($nome, '.sql')) {
                continue;
            }
            $lista[] = [
                'arquivo' => $nome,
                'tamanho_bytes' => $arq->getSize(),
                'tamanho_humano' => $this->formatarTamanho($arq->getSize()),
                'data' => date('d/m/Y H:i:s', $arq->getMTime()),
                'timestamp' => $arq->getMTime(),
            ];
        }

        usort($lista, fn (array $a, array $b) => $b['timestamp'] <=> $a['timestamp']);
        return $lista;
    }

    /**
     * Retorna o caminho seguro de um arquivo de backup para download.
     */
    public function obterCaminhoBackupSeguro(string $nomeArquivo): ?string
    {
        $nomeLimpo = basename($nomeArquivo);
        $caminho = storage_path('app/' . self::BACKUP_DIR . '/' . $nomeLimpo);
        if (File::exists($caminho) && is_file($caminho)) {
            return $caminho;
        }
        return null;
    }

    /**
     * Exporta estrutura e dados de todas as tabelas em SQL padrão via PDO.
     *
     * @param array<string, int> $resumoTabelas
     */
    private function gerarDumpSqlPortatil(array &$resumoTabelas = []): string
    {
        $driver = DB::getDriverName();
        $pdo = DB::connection()->getPdo();

        $linhas = [
            '-- ==========================================================================',
            '-- PEDELIVRE OS — BACKUP COMPLETO DO BANCO DE DADOS',
            '-- Gerado em: ' . now()->format('d/m/Y H:i:s') . ' | Driver: ' . $driver,
            '-- ==========================================================================',
            '',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        $tabelas = [];
        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($rows as $r) {
                $tabelas[] = (string) $r->name;
            }
        } else {
            $rows = DB::select('SHOW TABLES');
            foreach ($rows as $r) {
                $vals = array_values((array) $r);
                if (!empty($vals[0])) {
                    $tabelas[] = (string) $vals[0];
                }
            }
        }

        foreach ($tabelas as $tabela) {
            $linhas[] = "-- --------------------------------------------------------------------------";
            $linhas[] = "-- Tabela: `{$tabela}`";
            $linhas[] = "-- --------------------------------------------------------------------------";

            if ($driver === 'mysql' || $driver === 'mariadb') {
                try {
                    $createRow = (array) DB::selectOne("SHOW CREATE TABLE `{$tabela}`");
                    $createValues = array_values($createRow);
                    if (isset($createValues[1])) {
                        $linhas[] = "DROP TABLE IF EXISTS `{$tabela}`;";
                        $linhas[] = $createValues[1] . ";\n";
                    }
                } catch (Throwable) {}
            }

            $registros = DB::table($tabela)->get();
            $resumoTabelas[$tabela] = $registros->count();

            foreach ($registros as $reg) {
                $dados = (array) $reg;
                $colunas = array_map(fn ($col) => "`{$col}`", array_keys($dados));
                $valores = array_map(function ($val) use ($pdo): string {
                    if ($val === null) {
                        return 'NULL';
                    }
                    if (is_bool($val)) {
                        return $val ? '1' : '0';
                    }
                    if (is_int($val) || is_float($val)) {
                        return (string) $val;
                    }
                    return $pdo->quote((string) $val);
                }, array_values($dados));

                $linhas[] = sprintf(
                    'INSERT INTO `%s` (%s) VALUES (%s);',
                    $tabela,
                    implode(', ', $colunas),
                    implode(', ', $valores)
                );
            }

            $linhas[] = '';
        }

        $linhas[] = 'SET FOREIGN_KEY_CHECKS=1;';
        return implode("\n", $linhas);
    }

    private function limparBackupsAntigos(int $manterQtd): void
    {
        $backups = $this->listarBackups();
        if (count($backups) <= $manterQtd) {
            return;
        }

        $excedentes = array_slice($backups, $manterQtd);
        foreach ($excedentes as $item) {
            $caminho = $this->obterCaminhoBackupSeguro((string) $item['arquivo']);
            if ($caminho !== null) {
                @unlink($caminho);
            }
        }
    }

    /**
     * Resolve qual instância Evolution usar para disparar o alerta ao administrador.
     *
     * @param array<string, mixed> $cfg
     */
    private function resolverInstanciaAlerta(array $cfg): string
    {
        if (!empty($cfg['whatsapp_instance'])) {
            return (string) $cfg['whatsapp_instance'];
        }

        $primeiraLoja = Loja::withoutGlobalScopes()->orderBy('id')->first();
        if ($primeiraLoja !== null) {
            return $this->evolutionApi->obterNomeInstanciaLoja($primeiraLoja);
        }

        return 'pedelivre-loja-1';
    }

    /**
     * @param array<string, mixed> $incidente
     */
    private function registrarIncidenteNoHistorico(array $incidente): void
    {
        $path = storage_path('app/' . self::INCIDENTS_FILE);
        $lista = $this->listarIncidentesRecentes();
        array_unshift($lista, $incidente);
        $lista = array_slice($lista, 0, 40);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($lista, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarIncidentesRecentes(): array
    {
        $path = storage_path('app/' . self::INCIDENTS_FILE);
        if (!File::exists($path)) {
            return [];
        }
        $dados = json_decode((string) File::get($path), true);
        return is_array($dados) ? $dados : [];
    }

    private function formatarTamanho(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 2) . ' MB';
    }
}
