<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Service;
use Illuminate\Http\Request;

class EmployeController extends Controller
{
    public function getServices()
    {
        return response()->json(Service::all(), 200);
    }

    public function search(Request $request)
    {
        try {
            $term = $request->query('query', '');

            if (trim($term) === '') {
                return response()->json(
                    Employe::with('service')->get(),
                    200
                );
            }

            $results = Employe::with('service')
                ->where(function ($q) use ($term) {
                    $q->where('Nom_Prenom',     'LIKE', "%{$term}%")
                      ->orWhere('N_Tel',         'LIKE', "%{$term}%")
                      ->orWhere('N_PORT_TEL',    'LIKE', "%{$term}%")
                      ->orWhere('N_PORT_RESEAU', 'LIKE', "%{$term}%")
                      ->orWhere('login',         'LIKE', "%{$term}%");
                })
                ->orWhereHas('service', function ($q) use ($term) {
                    $q->where('Nom_Service', 'LIKE', "%{$term}%");
                })
                ->distinct()
                ->get();

            return response()->json($results, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Nom_Prenom'    => 'required|string|max:150',
            'login'         => 'nullable|string|max:100|unique:employe,login',
            'N_Tel'         => 'nullable|string|max:50',
            'N_PORT_TEL'    => 'nullable|string|max:100',
            'N_PORT_RESEAU' => 'nullable|string|max:100',
            'idService'     => 'required',
        ]);

        if (empty($validated['login'])) {
            $validated['login'] = strtolower(str_replace(' ', '.', $request->Nom_Prenom));
        }
        $validated['password'] = bcrypt('agent123');
        $validated['role']     = 'agent';

        $employe = Employe::create($validated);

        return response()->json([
            'message' => 'Employé ajouté avec succès',
            'data'    => $employe,
        ], 201);
    }

   public function update(Request $request, $id)
   {
       $employe = Employe::find($id);

       if (!$employe) {
           return response()->json(['message' => 'Employé introuvable'], 404);
       }


       $validated = $request->validate([
           'Nom_Prenom'    => 'sometimes|string|max:150',
           'N_Tel'         => 'nullable|string|max:50',
           'N_PORT_TEL'    => 'nullable|string|max:100',
           'N_PORT_RESEAU' => 'nullable|string|max:100',
           'idService'     => 'sometimes',
       ]);

       $employe->update($validated);
       $employe->load('service');

       return response()->json([
           'message' => 'Employé mis à jour',
           'data'    => $employe,
       ], 200);
   }
    public function destroy($id)
    {
        $employe = Employe::findOrFail($id);
        $employe->delete();
        return response()->json(['message' => 'Employé supprimé'], 200);
    }

    public function show($id)
    {
        $employe = Employe::with('service')->find($id);

        if (!$employe) {
            return response()->json(['message' => 'Employé introuvable.'], 404);
        }

        return response()->json($employe);
    }


    public function getAdminProfile()
    {
        $admin = auth()->user();

        if (!$admin) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        if (method_exists($admin, 'service')) {
            $admin->load('service');
        }

        return response()->json($admin, 200);
    }


    public function updateAdminProfile(Request $request)
    {
        $admin = auth()->user();

        if (!$admin) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $validated = $request->validate([
            'N_Tel' => 'nullable|string|max:50',
            'N_PORT_TEL' => 'nullable|string|max:50',
            'N_PORT_RESEAU' => 'nullable|string|max:50',
        ]);

        $employe = Employe::find($admin->id);
        $employe->update($validated);

        return response()->json($employe, 200);
    }

    public function updateAdminPassword(Request $request)
    {
        $admin = auth()->user();

        if (!$admin) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $request->validate([
            'old_password' => 'required|string',
            'password'     => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min'       => 'Le nouveau mot de passe doit faire au moins 6 caractères.'
        ]);

        $employe = Employe::find($admin->id);

        if (!\Hash::check($request->old_password, $employe->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.'
            ], 422);
        }

        $employe->password = \Hash::make($request->password);
        $employe->save();

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.'
        ], 200);
    }



    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,agent',
        ]);

        $employe = Employe::findOrFail($id);
        $employe->role = $request->role;
        $employe->save();

        return response()->json([
            'message' => $request->role === 'admin'
                ? "{$employe->Nom_Prenom} est désormais Administrateur."
                : "{$employe->Nom_Prenom} repasse au rôle Agent.",
            'data' => $employe,
        ], 200);
    }

}
