<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'payment_id',
        'event',
        'amount',
        'currency',
        'user_id',
        'timestamp',
        'received_at',
    ];
    public $timestamps = false;
}
