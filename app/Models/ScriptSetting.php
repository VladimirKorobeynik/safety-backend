<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScriptSetting extends Model
{
    use HasFactory;

    protected $table = 'settings';
    protected $primaryKey = 'setting_id';
    public $timestamps = false;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'script_id',
        'user_sensor_id',
        'is_active'
    ];
}
