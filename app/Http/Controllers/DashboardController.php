<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Tip;
use App\Models\TipCategory;
use App\Models\Meal;
use App\Models\MealCategory;
use App\Models\Advertisement;
use App\Models\Forum;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function recentActivity()
    {
        $logs = \App\Models\SystemLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($logs);
    }

    public function index()
    {
        // Build stats in the structure expected by the frontend
        $stats = [
            'users' => [
                'total' => User::count(),
                'paying' => User::where('type', 'payed')->count(),
                'subscribed' => class_exists(\App\Models\SubscribedUser::class)
                    ? \App\Models\SubscribedUser::count()
                    : User::where('type', 'subscribed')->count(),
            ],
            'doctors' => [
                'total' => Doctor::count(),
                'pending' => Doctor::where('application_status', 'pending')->count(),
            ],
            'content' => [
                'tips' => class_exists(Tip::class) ? Tip::count() : 0,
                'meals' => class_exists(Meal::class) ? Meal::count() : 0,
                'forums' => class_exists(Forum::class) ? Forum::count() : 0,
            ],
            'system' => [
                'tips_categories' => class_exists(TipCategory::class) ? TipCategory::count() : 0,
                'meal_categories' => class_exists(MealCategory::class) ? MealCategory::count() : 0,
                'ads' => class_exists(Advertisement::class) ? Advertisement::count() : 0,
            ],
            'engagement' => [
                'messages' => class_exists(Message::class) ? Message::count() : 0,
                'consultations' => Consultation::count(),
                'pending_consultations' => Consultation::where('status', 'pending')->count(),
            ],
            'revenue' => [
                'total' => Invoice::where('payment_status', 'paid')->sum('amount'),
                'pending_invoices' => Invoice::where('payment_status', 'pending')->count(),
                'active_subscriptions' => Subscription::where('status', 'active')->count(),
            ],
        ];

        // Get recent consultations
        $recent_consultations = Consultation::with(['doctor.user', 'patient.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get monthly revenue chart data (last 6 months)
        $monthly_revenue = Invoice::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_consultations' => $recent_consultations,
            'monthly_revenue' => $monthly_revenue,
        ]);
    }
}
