<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSensor extends Model
{
    use HasFactory;

    protected $table = 'user_sensors';
    protected $primaryKey = 'user_sensor_id';

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_sensor_id',
        'sensor_id',
        'house_id',
        'user_id',
        'name',
        'value',
        'smart_device_id',
        'is_active',
        'is_activated',
        'activate_date',
        'activation_key',
    ];
}
