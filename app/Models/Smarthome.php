<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smarthome extends Model
{
    use HasFactory;
    
    protected $dateFormat = 'Y-m-d\TH:i:s.u';
    
    protected $table = 'smarthomes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'description', 
        'properties'
    ];

    protected $casts = [
        'properties' => 'array'
    ];
}
