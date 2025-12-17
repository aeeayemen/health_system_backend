<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DietController;
use App\Http\Controllers\DietNoteController;
use App\Http\Controllers\DietComponentController;
use App\Http\Controllers\WeeklyCalculationController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\TipCategoryController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\MealCategoryController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\MedicalTestController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // === Dashboard ===
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);
    Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);

    // === Reports ===
    Route::get('/reports/usage', [ReportController::class, 'usage']);
    Route::get('/reports/ratings', [ReportController::class, 'ratings']);
    Route::get('/reports/diets', [ReportController::class, 'diets']);
    Route::get('/reports/measurements', [ReportController::class, 'measurements']);

    // === Users ===
    Route::get('/users/payed', [UserController::class, 'payedUsers']);
    Route::get('/users/normal', [UserController::class, 'normalUsers']);
    Route::apiResource('users', UserController::class);
    Route::post('/users/{id}/toggle-ban', [UserController::class, 'toggleBan']);

    // === Doctors ===
    Route::get('doctors/approved', [DoctorController::class, 'approvedApplications']);
    Route::get('doctors/pending', [DoctorController::class, 'pendingApplications']);
    Route::post('doctors/{doctor}/approve', [DoctorController::class, 'approveApplication']);
    Route::post('doctors/{doctor}/reject', [DoctorController::class, 'rejectApplication']);
    Route::post('doctors/{id}/status', [DoctorController::class, 'updateStatus']);
    Route::apiResource('doctors', DoctorController::class);

    // === Patients ===
    Route::apiResource('patients', PatientController::class);

    // === Diet Plans (Diets) ===
    Route::apiResource('diet-plans', DietController::class); // Mapping /diet-plans to DietController
    Route::apiResource('diets', DietController::class);
    Route::apiResource('diet-notes', DietNoteController::class);
    Route::apiResource('diet-components', DietComponentController::class);

    // === Weekly Calculations ===
    Route::apiResource('weekly-calculations', WeeklyCalculationController::class);

    // === Rates ===
    // === Rates ===
    Route::apiResource('rates', RateController::class);

    // === Measurements ===
    Route::apiResource('measurements', MeasurementController::class);

    // === Consultations ===
    Route::apiResource('consultations', ConsultationController::class);
    Route::put('/consultations/{id}/status', [ConsultationController::class, 'updateStatus']); // Assuming updateStatus exists

    // === Subscriptions ===
    Route::apiResource('subscriptions', SubscriptionController::class);

    // === Invoices ===
    Route::apiResource('invoices', InvoiceController::class);

    // === Content Management ===
    Route::apiResource('tips', TipController::class);
    Route::apiResource('tips-categories', TipCategoryController::class);
    Route::apiResource('meals', MealController::class);
    Route::apiResource('meal-categories', MealCategoryController::class);
    Route::apiResource('advertisements', AdvertisementController::class);

    // === Forums ===
    Route::apiResource('forums', ForumController::class);
    Route::get('/forums/{forumId}/members', [ForumController::class, 'getMembers']);
    Route::post('/forums/{forumId}/users', [ForumController::class, 'addUser']);
    Route::delete('/forums/{forumId}/users/{userId}', [ForumController::class, 'removeUser']);

    // === Medical Tests ===
    Route::apiResource('medical-tests', MedicalTestController::class);
    Route::put('/medical-tests/{id}/status', [MedicalTestController::class, 'updateStatus']);

    // === Chat ===
    Route::get('/chat/conversations', [ChatController::class, 'getConversations']);
    Route::get('/chat/conversations/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages', [ChatController::class, 'sendMessage']);
    // Admin Chat
    Route::get('/admin/chats', [ChatController::class, 'getAllConversations']);
    Route::delete('/admin/chats/{id}', [ChatController::class, 'deleteConversation']);
    Route::delete('/admin/messages/{id}', [ChatController::class, 'deleteMessage']);

    // === Settings ===
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);

    // === System ===
    Route::get('/logs', [LogController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // === Subscribed Users ===
    Route::apiResource('users-subscribed', \App\Http\Controllers\SubscribedUserController::class);

    // === Main Calculations ===
    Route::apiResource('main-calculations', \App\Http\Controllers\MainCalculationController::class);
});
