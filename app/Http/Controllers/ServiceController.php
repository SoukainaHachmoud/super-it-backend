<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        try {
            $services = Service::all();
            return response()->json($services, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'Nom_Service' => 'required|string|max:255',
            ]);

            $service = Service::create([
                'Nom_Service' => $request->Nom_Service
            ]);

            return response()->json($service, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'Nom_Service' => 'required|string|max:255',
            ]);

            $service = Service::findOrFail($id);

            $service->update([
                'Nom_Service' => $request->Nom_Service
            ]);

            return response()->json([
                'message' => 'Service modifié avec succès !',
                'service' => $service
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $service = Service::findOrFail($id);
            $service->delete();

            return response()->json(['message' => 'Service supprimé !'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => "Impossible de supprimer le service. Vérifie s'il est lié à des agents.",
                'details' => $e->getMessage()
            ], 500);
        }
    }




}
