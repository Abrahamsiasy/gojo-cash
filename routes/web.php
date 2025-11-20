<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Settings;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;

Route::view('/', 'home')
    ->name('home');

Route::get('home', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified']);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {
        Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
        Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
        Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
        Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
        Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
        Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');

        Route::resource('companies', CompanyController::class);
        Route::resource('accounts', AccountController::class);
        Route::get('accounts/{account}/export-transactions', [AccountController::class, 'exportTransactions'])->name('accounts.export-transactions');
        Route::resource('transaction-categories', TransactionCategoryController::class);
        Route::resource('transactions', TransactionController::class);
        Route::resource('banks', BankController::class);
        Route::resource('clients', ClientController::class);

    });

require __DIR__.'/telegram.php';

require __DIR__.'/auth.php';
