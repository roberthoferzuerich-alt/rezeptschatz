<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncMutationLog extends Model
{
    use HasFactory;

    protected $table = 'sync_mutations_log';

    protected $fillable = [
        'user_id',
        'mutation_id',
        'entity_type',
        'entity_id',
        'action',
        'applied_version',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'applied_version' => 'integer',
            'response_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

