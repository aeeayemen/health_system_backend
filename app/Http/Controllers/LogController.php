<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::with('user');
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }
        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }
}
