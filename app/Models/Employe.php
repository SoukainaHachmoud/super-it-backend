<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Employe extends Authenticatable
{
    use HasApiTokens;

    protected $table      = 'employe';
    protected $primaryKey = 'id';

    protected $fillable = [
        'Nom_Prenom', 'login', 'password', 'role',
        'N_Tel', 'N_PORT_TEL', 'N_PORT_RESEAU', 'idService',
    ];

    protected $hidden = ['password'];

    public function service()
    {
        return $this->belongsTo(Service::class, 'idService', 'idService');
    }
}
