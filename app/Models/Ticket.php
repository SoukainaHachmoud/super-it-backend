<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'employe_id',
        'sujet',
        'description',
        'categorie',
        'urgence',
        'statut',
    ];


    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at', 'asc');
    }

    public function histories()
    {
        return $this->hasMany(TicketHistory::class)->orderBy('created_at', 'asc');
    }
}
