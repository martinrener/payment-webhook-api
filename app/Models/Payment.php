<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EventLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

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
