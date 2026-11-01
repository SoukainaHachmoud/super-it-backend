<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $table = 'service';
    protected $primaryKey = 'idService';

    public $timestamps = false;

    protected $fillable = ['Nom_Service'];





    public function employers(): HasMany
    {
        return $this->hasMany(Employer::class, 'idService', 'idService');
    }
}
