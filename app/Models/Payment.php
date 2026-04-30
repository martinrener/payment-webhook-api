<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EventLog;

class Payment extends Model
{
    protected $fillable = [
        'payment_id',
        'event',
        'amount',
        'currency',
        'user_id',
        'last_event_id',
    ];

    public function lastEvent()
    {
        return $this->belongsTo(EventLog::class, 'last_event_id', 'event_id');
    }
}
