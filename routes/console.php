<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console & Scheduled Commands - PedeLivre SaaS
|--------------------------------------------------------------------------
*/

// 1. Monitoramento Contínuo de Saúde & Alerta de Queda (E-mail + WhatsApp) a cada 5 minutos
Schedule::command('pedelivre:monitorar-saude')->everyFiveMinutes();

// 2. Backup Completo Automático do Sistema (SQL + JSON em .ZIP enviado por E-mail) todos os dias às 03:00
Schedule::command('pedelivre:backup')->dailyAt('03:00');

// 3. Régua Diária de Cobrança e Inadimplência SaaS (3, 7 e 15 dias)
Schedule::command('pedelivre:processar-inadimplencia')->dailyAt('06:00');

// 4. Recuperação de PIX pendente e cancelamento de expirados a cada 5 minutos
Schedule::command('pedelivre:recuperar-carrinhos')->everyFiveMinutes();

// 5. Verificação de atrasos na cozinha e check-in automático de entrega com o cliente a cada 5 minutos
Schedule::command('pedelivre:verificar-atrasos')->everyFiveMinutes();

// 6. Disparo de pesquisas de satisfação NPS e avaliação 5 estrelas Google Maps a cada 5 minutos
Schedule::command('pedelivre:solicitar-avaliacoes')->everyFiveMinutes();

// 7. Campanhas automáticas de reativação de clientes (Win-Back) às quintas e sextas às 17:30
Schedule::command('pedelivre:disparar-winback')->weeklyOn([4, 5], '17:30');
