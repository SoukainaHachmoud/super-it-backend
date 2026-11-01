<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * POST /api/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Nom_Prenom' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:employe,login',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = Employe::create([
            'Nom_Prenom' => trim($request->input('Nom_Prenom')),
            'login' => trim($request->input('login')),
            'password' => Hash::make($request->input('password')), // Hachage propre par Laravel
            'role' => 'agent',
        ]);

        return response()->json(['message' => 'Compte créé avec succès !', 'user' => $user], 201);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        $user = Employe::where('login', $request->login)->first();

        if (!$user) {
            return response()->json(['message' => "Cet identifiant n'existe pas."], 401);
        }


        $passwordValide = Hash::check($request->password, $user->password) || ($request->password === $user->password);

        if (!$passwordValide) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'login'      => $user->login,
                'role'       => $user->role,
                'Nom_Prenom' => $user->Nom_Prenom,
                'employe_id' => (string)$user->id
            ]
        ]);
    }


public function updatePassword(Request $request)
{
    $request->validate([
        'old_password' => 'required|string',
        'password' => 'required|string|min:6|confirmed', // 'confirmed' valide automatiquement 'password_confirmation'
    ], [
        'old_password.required' => 'Le mot de passe actuel est requis.',
        'password.required' => 'Le nouveau mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        'password.confirmed' => 'Les mots de passe ne correspondent pas.'
    ]);

    $user = $request->user();

    $passwordValide = Hash::check($request->old_password, $user->password) || ($request->old_password === $user->password);

    if (!$passwordValide) {
        return response()->json(['message' => 'Le mot de passe actuel est incorrect.'], 422);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json(['message' => 'Votre mot de passe a été mis à jour avec succès !'], 200);
}
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }


}
