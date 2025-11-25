<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Settings;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);

    });

require __DIR__.'/telegram.php';

require __DIR__.'/auth.php';

Route::middleware(['web'])
    ->withoutMiddleware([\App\Http\Middleware\CheckInstallation::class])
    ->group(function () {
        Route::get('install/step1', [App\Http\Controllers\InstallController::class, 'database'])->name('install.step1');
        Route::post('install/step1', [App\Http\Controllers\InstallController::class, 'storeDatabase'])->name('install.step1.store');
    });

Route::middleware(['web'])->group(function () {
    Route::get('install/reset', [App\Http\Controllers\InstallController::class, 'reset'])->name('install.reset');
    Route::get('install/step2', [App\Http\Controllers\InstallController::class, 'step2'])->name('install.step2');
    Route::post('install/step2', [App\Http\Controllers\InstallController::class, 'storeMigrate'])->name('install.step2.store');
    Route::get('install/step3', [App\Http\Controllers\InstallController::class, 'step3'])->name('install.step3');
    Route::post('install/step3', [App\Http\Controllers\InstallController::class, 'storeAdmin'])->name('install.step3.store');
    Route::get('install/step4', [App\Http\Controllers\InstallController::class, 'step4'])->name('install.step4');
    Route::post('install/step4', [App\Http\Controllers\InstallController::class, 'storeCompany'])->name('install.step4.store');
    Route::post('install/step5', [App\Http\Controllers\InstallController::class, 'testCreateCompany'])->name('install.step5.store');
});
