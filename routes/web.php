<?php

use App\Http\Controllers\Admin\EnquiryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'pages.home')->name('home');
Route::view('about', 'pages.about')->name('about');
Route::view('services', 'pages.services')->name('services');
Route::view('programs', 'pages.programs')->name('programs');
Route::view('booking', 'pages.booking')->name('booking');
Volt::route('contact', 'contact')->name('contact');

Route::get('dashboard', function (Request $request) {
    return redirect()->route($request->user()->dashboardRouteName());
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('client/dashboard', 'dashboards.client')
        ->middleware('role:client')
        ->name('client.dashboard');

    Route::prefix('client')->middleware('role:client')->name('client.')->group(function () {
        Volt::route('appointments/book', 'client.book-appointment')->name('appointments.book');
        Volt::route('appointments', 'client.appointments')->name('appointments.index');
    });

    Route::view('counsellor/dashboard', 'dashboards.counsellor')
        ->middleware('role:counsellor')
        ->name('counsellor.dashboard');

    Route::prefix('counsellor')->middleware('role:counsellor')->name('counsellor.')->group(function () {
        Volt::route('availability', 'counsellor.availability')->name('availability');
        Volt::route('appointments', 'counsellor.appointments')->name('appointments.index');
    });

    Route::prefix('admin')->middleware('role:administrator')->name('admin.')->group(function () {
        Route::view('dashboard', 'dashboards.admin')->name('dashboard');
        Route::get('enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
        Volt::route('booking-setup', 'admin.booking-setup')->name('booking-setup');
        Volt::route('appointments', 'admin.appointments')->name('appointments.index');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
