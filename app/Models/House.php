<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
	    'user_id',
        'name',
        'address',
        'count_rooms',
        'count_windows',
        'count_doors'
    ];

    protected $primaryKey = 'house_id';
    public $timestamps = false;

    public function getHouseOwner() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
