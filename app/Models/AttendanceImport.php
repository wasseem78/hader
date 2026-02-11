<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceImport extends Model
{
    protected $fillable = [
        'device_id',
        'raw_data',
        'status',
        'error_message',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
