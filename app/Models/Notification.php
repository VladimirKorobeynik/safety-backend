<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
	    'user_id',
        'title',
        'content',
        'time'
    ];

    protected $primaryKey = 'notification_id';
    public $timestamps = false;

}
