<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwitchModel extends Model
{
    use HasFactory;

    protected $table = 'switches';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
     public $timestamps = false;

    protected $fillable = [
        'id',
        'nom',
        'port_start',
        'port_end',
        'color'
    ];
}
