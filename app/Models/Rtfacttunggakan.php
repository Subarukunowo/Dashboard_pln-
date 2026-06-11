<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtImportLogs extends Model
{
    protected $table = 'rt_import_logs';

    protected $fillable = [
        'filename',
        'sheet_name',
        'tahun',
        'bulan',
        'rows_inserted',
        'rows_updated',
        'rows_skipped',
        'catatan',
        'user_id',
    ];

    protected $casts = [
        'tahun'         => 'integer',
        'bulan'         => 'integer',
        'rows_inserted' => 'integer',
        'rows_updated'  => 'integer',
        'rows_skipped'  => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }
}