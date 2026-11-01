<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employe;
use App\Models\Service;

class DashboardController extends Controller
{
    public function getStats()
    {
        try {

            $totalEmployes = Employe::count();
            $totalServices = Service::count();

            return response()->json([
                'total_employes' => $totalEmployes,
                'total_services' => $totalServices
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du calcul des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
