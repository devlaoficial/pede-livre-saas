<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console & Scheduled Commands - PedeLivre SaaS
|--------------------------------------------------------------------------
*/

// Daily SaaS delinquency state machine (3, 7, and 15 days overdue)
Schedule::command('pedelivre:processar-inadimplencia')->dailyAt('06:00');

// Recover pending PIX and cancel expired orders every 5 minutes
Schedule::command('pedelivre:recuperar-carrinhos')->everyFiveMinutes();

// Check kitchen peak delays and trigger de-escalating notifications
Schedule::command('pedelivre:verificar-atrasos')->everyFiveMinutes();
