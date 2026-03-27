<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DietController;
use App\Http\Controllers\DietPlanController;
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
use App\Http\Controllers\MedicalFileController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\CalculationController;
use App\Http\Controllers\ForumPostController;
use App\Http\Controllers\DietPeriodController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\DietTypeController;
use App\Http\Controllers\AthkarController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =============================================
// PUBLIC ROUTES (No Authentication Required)
// =============================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmailPublic']);
Route::get('/ping', [AuthController::class, 'ping']);

// === Public Content (Guest Access) ===
Route::prefix('public')->group(function () {
    Route::get('/tips', [PublicController::class, 'tips']);
    Route::get('/doctors', [PublicController::class, 'doctors']);
    Route::get('/ads', [PublicController::class, 'advertisements']);
    Route::get('/forums', [PublicController::class, 'forums']);
    Route::get('/forums/{forumId}/posts', [PublicController::class, 'forumPosts']);
    Route::get('/diets-preview', [PublicController::class, 'dietsPreview']);
    Route::get('/athkar', [AthkarController::class, 'index']);
    Route::get('/athkar-raw', function () {
        return \App\Models\Athkar::all();
    });
    Route::get('/debug-users', function () {
        return \App\Models\User::all();
    });
});

// =============================================
// PROTECTED ROUTES (Authentication Required)
// =============================================

Route::middleware('auth:sanctum')->group(function () {
    // === Email Verification ===
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail']);
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');
});
// , 'verified'
Route::middleware(['auth:sanctum'])->group(function () {
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

    // === Health Calculations ===
    Route::prefix('calculations')->group(function () {
        Route::post('/bmi', [CalculationController::class, 'calculateBMI']);
        Route::post('/bmr', [CalculationController::class, 'calculateBMR']);
        Route::post('/calories', [CalculationController::class, 'calculateCalories']);
        Route::post('/nutrition', [CalculationController::class, 'calculateNutrition']);
        Route::post('/exchange-list', [CalculationController::class, 'calculateExchangeList']);
        Route::get('/history', [CalculationController::class, 'history']);
    });

    // === Patient Diet (My Diet) ===
    Route::prefix('my-diet')->group(function () {
        Route::get('/', [DietController::class, 'myDiet']);
        Route::get('/periods', [DietController::class, 'myDietPeriods']);
        Route::get('/meals', [DietController::class, 'myDietMeals']);
        Route::get('/report', [DietController::class, 'myDietReport']);
    });

    // === Doctors ===
    Route::get('doctors/approved', [DoctorController::class, 'approvedApplications']);
    Route::get('doctors/pending', [DoctorController::class, 'pendingApplications']);
    Route::post('doctors/{doctor}/approve', [DoctorController::class, 'approveApplication']);
    Route::post('doctors/{doctor}/reject', [DoctorController::class, 'rejectApplication']);
    Route::post('doctors/{id}/status', [DoctorController::class, 'updateStatus']);
    Route::apiResource('doctors', DoctorController::class);

    // === Doctor Profile & Management ===
    Route::prefix('doctor')->group(function () {
        Route::get('/profile', [DoctorController::class, 'myProfile']);
        Route::put('/profile', [DoctorController::class, 'updateMyProfile']);
        Route::get('/rates', [DoctorController::class, 'myRates']);
        Route::get('/patients', [DoctorController::class, 'myPatients']);
        Route::get('/patients/{id}', [PatientController::class, 'show']);
        Route::get('/patients/{patientId}/progress', [DoctorController::class, 'patientProgress']);
        Route::get('/patients/{patientId}/calculations', [DoctorController::class, 'patientCalculations']);
    });

    // === Patients ===
    Route::get('patients/profile', [PatientController::class, 'myProfile']);
    Route::put('patients/profile', [PatientController::class, 'updateMyProfile']);
    Route::post('patients/profile', [PatientController::class, 'updateMyProfile']); // Support POST for easier image uploads
    Route::get('patients/my-doctors', [PatientController::class, 'myDoctors']);
    Route::apiResource('patients', PatientController::class)->where(['patient' => '[0-9]+']);

    // === Diet Plans (Diets) ===
    Route::apiResource('diet-plans', DietPlanController::class);
    Route::apiResource('diets', DietController::class);
    Route::put('/diets/{id}/status', [DietController::class, 'updateStatus']);
    Route::apiResource('diet-notes', DietNoteController::class);
    Route::apiResource('diet-components', DietComponentController::class);
    Route::apiResource('diet-periods', DietPeriodController::class);

    // === Diet Types ===
    Route::apiResource('diet-types', DietTypeController::class);
    Route::post('/diet-types/{dietType}/toggle-status', [DietTypeController::class, 'toggleStatus']);

    // === Weekly Calculations ===
    Route::apiResource('weekly-calculations', WeeklyCalculationController::class);

    // === Rates ===
    Route::apiResource('rates', RateController::class);
    Route::post('/doctors/{doctorId}/rate', [RateController::class, 'rateDoctor']);
    Route::get('/doctors/{doctorId}/rates', [RateController::class, 'getDoctorRates']);
    Route::get('/my-rates', [RateController::class, 'myRates']);

    // === Measurements ===
    Route::apiResource('measurements', MeasurementController::class);

    // === Consultations ===
    Route::apiResource('consultations', ConsultationController::class);
    Route::put('/consultations/{id}/status', [ConsultationController::class, 'updateStatus']);

    // === Subscriptions ===
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::put('/subscriptions/{id}/status', [SubscriptionController::class, 'updateStatus']);

    // === Invoices ===
    Route::apiResource('invoices', InvoiceController::class);
    Route::put('/invoices/{id}/status', [InvoiceController::class, 'updateStatus']);

    // === Payments ===
    Route::apiResource('payments', PaymentController::class);
    Route::put('/payments/{id}/status', [PaymentController::class, 'updateStatus']);

    // === Payment Methods ===
    Route::apiResource('payment-methods', PaymentMethodController::class);
    Route::post('/payment-methods/{id}/set-default', [PaymentMethodController::class, 'setDefault']);

    // === Reminders ===
    Route::apiResource('reminders', ReminderController::class);

    // === Content Management ===
    Route::apiResource('tips', TipController::class);
    Route::apiResource('tips-categories', TipCategoryController::class);
    Route::apiResource('meals', MealController::class);
    Route::apiResource('meal-categories', MealCategoryController::class);
    Route::apiResource('advertisements', AdvertisementController::class);
    Route::apiResource('athkar', AthkarController::class);
    Route::get('/athkar/categories', [AthkarController::class, 'categories']);
    // === Medical References ===
    Route::get('/references/nutrition-manuals', [\App\Http\Controllers\RefController::class, 'nutritionManuals']);

    // === Forums ===
    Route::apiResource('forums', ForumController::class);
    Route::get('/forums/{forumId}/members', [ForumController::class, 'getMembers']);
    Route::post('/forums/{forumId}/users', [ForumController::class, 'addUser']);
    Route::delete('/forums/{forumId}/users/{userId}', [ForumController::class, 'removeUser']);
    Route::post('/forums/{forumId}/join', [ForumController::class, 'join']);
    Route::post('/forums/{forumId}/leave', [ForumController::class, 'leave']);

    // === Forum Posts ===
    Route::get('/forums/{forumId}/posts', [ForumPostController::class, 'index']);
    Route::post('/forums/{forumId}/posts', [ForumPostController::class, 'store']);
    Route::get('/posts/{id}', [ForumPostController::class, 'show']);
    Route::put('/posts/{id}', [ForumPostController::class, 'update']);
    Route::delete('/posts/{id}', [ForumPostController::class, 'destroy']);
    Route::post('/posts/{id}/like', [ForumPostController::class, 'like']);
    Route::post('/posts/{id}/unlike', [ForumPostController::class, 'unlike']);

    // === Medical Tests ===
    Route::apiResource('medical-tests', MedicalTestController::class);
    Route::put('/medical-tests/{id}/status', [MedicalTestController::class, 'updateStatus']);

    // === Medical Files ===
    Route::get('/medical-files/{id}/download', [MedicalFileController::class, 'download']);
    Route::apiResource('medical-files', MedicalFileController::class);

    // === Chat ===
    Route::get('/chat/conversations', [ChatController::class, 'getConversations']);
    Route::get('/chat/conversations/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages', [ChatController::class, 'sendMessage']);
    Route::post('/chat/messages/{id}/read', [ChatController::class, 'markAsRead']);
    Route::post('/chat/conversations/{conversationId}/read', [ChatController::class, 'markConversationAsRead']);
    Route::get('/chat/history/{userId}', [ChatController::class, 'getHistory']);
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount']);
    // Admin Chat
    Route::get('/admin/chats', [ChatController::class, 'getAllConversations']);
    Route::delete('/admin/chats/{id}', [ChatController::class, 'deleteConversation']);
    Route::delete('/admin/messages/{id}', [ChatController::class, 'deleteMessage']);

    // === Settings ===
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);

    // === System ===
    Route::get('/logs', [LogController::class, 'index']);

    // === Notifications ===
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/my', [NotificationController::class, 'myNotifications']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // === Subscribed Users ===
    Route::apiResource('users-subscribed', \App\Http\Controllers\SubscribedUserController::class);

    // === Main Calculations ===
    Route::apiResource('main-calculations', \App\Http\Controllers\MainCalculationController::class);
});
