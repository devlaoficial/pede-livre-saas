<?php

declare(strict_types=1);

namespace App\Domain\Pagamentos\Models;

use Illuminate\Database\Eloquent\Model;

final class WebhookLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'webhook_logs';

    protected $fillable = [
        'origem',
        'payload',
        'processado',
        'erro',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'encrypted:array',
        'processado' => 'boolean',
        'created_at' => 'datetime',
    ];
}
