<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use Illuminate\Http\Request;

class AlerteController extends Controller
{
    public function index()
    {
        $alertes = Alerte::orderBy('created_at', 'desc')->get();
        return response()->json($alertes);
    }
    public function getActive(Request $request)
    {
        $alerte = Alerte::where('actif', true)
                        ->whereNull('service_id')
                        ->latest()
                        ->first();

        return response()->json($alerte);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'gravite' => 'required|string',
        ]);

        Alerte::where('actif', true)
              ->whereNull('service_id')
              ->update(['actif' => false]);

        $alerte = Alerte::create([
            'message'    => $request->message,
            'gravite'    => $request->gravite,
            'service_id' => null,
            'actif'      => true
        ]);

        return response()->json($alerte, 201);
    }

    public function desactiver()
    {
        Alerte::where('actif', true)->update(['actif' => false]);
        return response()->json(['message' => 'Alerte générale désactivée avec succès.']);
    }
}
