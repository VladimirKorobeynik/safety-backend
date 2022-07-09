<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSensorStatistic extends Model
{
    use HasFactory;

    protected $table = 'sensor_statistics';
    protected $primaryKey = 'sensor_stat_id';

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sensor_stat_id',
        'user_sensor_id',
        'sensor_value',
        'created_at',
        'updated_at',
    ];
}
