<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartDevice extends Model
{
    use HasFactory;

    protected $table = 'smart_devices';
    protected $primaryKey = 'smt_dev_id';
    public $timestamps = false;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'smart_device_number',
        'count_gas_sensors',
        'count_humidity_sensors',
        'count_smoke_sensors',
        'count_motion_sensors',
        'is_bought',
        'is_activated',
    ];
}
