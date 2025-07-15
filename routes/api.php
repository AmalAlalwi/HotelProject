<?php

use App\Http\Controllers\Api\Admin\RoomController;

use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\Admin\StatisticsController;
use App\Http\Controllers\Api\User\BookingController;
use App\Http\Controllers\Api\User\BookingServiceController;
use App\Http\Controllers\Api\User\InvoiceController;
use App\Http\Controllers\Api\User\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\User\MessageController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
//-----------------------------api auth-----------------------------------------------------------

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth_jwt')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    });

//------------------------------CRUD Rooms--------------------------------------------------------

Route::middleware(['auth_jwt','admin'])->group(function () {
//-------------------------------Rooms api-------------------------------------------
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms/{room}', [RoomController::class, 'update']);
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    Route::get('/rooms/{room}', [RoomController::class, 'show']);
//-------------------------------Service api----------------------------------------
    Route::apiResource('/services', ServiceController::class);
    //---------------------------statistics-----------------------------------------
    Route::get('/admin/statistics',[StatisticsController::class,'index']);
    Route::get('/admin/invoices',[StatisticsController::class,'getAllInvoicesWithItems']);
    Route::get('/admin/invoices/paid',[StatisticsController::class,'getPaidInvoicesWithItems']);
    Route::get('/admin/invoices/unpaid',[StatisticsController::class,'getUnpaidInvoicesWithItems']);
    Route::get('/admin/invoices/partial',[StatisticsController::class,'getPartialInvoicesWithItems']);
    Route::get('/admin/statistics/revenue',[StatisticsController::class,'revenueStatus']);
});

Route::middleware(['auth_jwt'])->group(function () {
    Route::apiResource('booking', BookingController::class);
    Route::apiResource('bookingService', BookingServiceController::class);
    Route::get('/getrooms', [\App\Http\Controllers\Api\User\RoomController::class, 'index']);
    Route::get('/getservices', [\App\Http\Controllers\Api\User\RoomController::class, 'indexService']);
    Route::post('/sendMessage', [MessageController::class, 'sendMessage']);
    Route::get('/conversation/{receiverId}', [MessageController::class, 'getConversation']);
    Route::get('/conversations/{employeeId}', [MessageController::class, 'getAllConversations']);

    //----------------------------------Invoices----------------------------------------------
    Route::get('/invoices/paid', [InvoiceController::class, 'getPaidInvoices']);
    Route::get('/invoices/unpaid', [InvoiceController::class, 'getUnpaidInvoices']);
    Route::get('/invoices/partial', [InvoiceController::class, 'getPartialInvoices']);
    Route::get('/invoices/{id}/show', [InvoiceController::class, 'showInvoice']);
    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPDF']);
    Route::post('/invoices/{id}/simulate', [InvoiceController::class, 'simulatePayment']);

    //---------------------------------Notifications-------------------------------------------
    Route::get('/notifications/unread', [NotificationController::class, 'unreadNotifications']);
    Route::get('/notifications/read', [NotificationController::class, 'readNotifications']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

});
