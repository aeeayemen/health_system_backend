<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DietPlan;
use App\Models\Measurement;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function usage(Request $request)
    {
        $data = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();
        return response()->json($data);
    }

    public function ratings(Request $request)
    {
        return response()->json([]);
    }

    public function diets(Request $request)
    {
        // Assuming DietPlan has patients relationship (hasManyThrough or hasMany)
        // If not, we can count manually.
        // For now, just return all diet plans.
        $data = DietPlan::all();
        return response()->json($data);
    }

    public function measurements(Request $request)
    {
        $data = Measurement::orderBy('created_at', 'desc')->limit(100)->get();
        return response()->json($data);
    }
}
