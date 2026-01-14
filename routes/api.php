<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
// Example API route
Route::controller(DashboardController::class)->group(function () {
    // Route::post('booking-details', 'bookingDetails')->name('booking_details');
});
