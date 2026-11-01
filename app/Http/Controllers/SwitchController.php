<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\SwitchModel;
use Illuminate\Http\Request;

class SwitchController extends Controller
{
    public function index()
    {
        $switches = SwitchModel::orderBy('id', 'asc')->get();
        return response()->json($switches);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:5|unique:switches,id',
            'nom' => 'required|string|max:100',
            'port_start' => 'required|integer|min:0',
            'port_end' => 'required|integer|gt:port_start',
            'color' => 'required|string|max:10',
        ]);

        $validated['id'] = strtoupper($validated['id']);

        $switch = SwitchModel::create($validated);

        return response()->json([
            'message' => 'Switch ajouté avec succès !',
            'data' => $switch
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $switch = SwitchModel::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'port_start' => 'required|integer|min:0',
            'port_end' => 'required|integer|gt:port_start',
            'color' => 'required|string|max:10',
        ]);

        $switch->update($validated);

        return response()->json([
            'message' => 'Switch mis à jour avec succès !',
            'data' => $switch
        ]);
    }

    public function destroy($id)
    {
        $switch = SwitchModel::findOrFail($id);
        $switch->delete();

        return response()->json([
            'message' => 'Switch supprimé avec succès !'
        ]);
    }

 public function updateAgentPort(Request $request, $idEmploye)
     {
         $validated = $request->validate([
             'N_PORT_RESEAU' => 'nullable|string|max:255',
         ]);

         $agent = \App\Models\Employe::findOrFail($idEmploye);

         $agent->N_PORT_RESEAU = $validated['N_PORT_RESEAU'] ? strtoupper(trim($validated['N_PORT_RESEAU'])) : null;
         $agent->save();

         return response()->json([
             'message' => 'Affectation réseau mise à jour.',
             'agent' => $agent
         ], 200);
     }
}
