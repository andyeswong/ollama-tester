<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OllamaServerController;
use App\Http\Controllers\OllamaModelTestController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Ollama Server Routes
Route::prefix('ollama')->name('ollama.')->middleware(['auth'])->group(function () {
    // Server routes
    Route::resource('servers', OllamaServerController::class);
    Route::get('servers/{server}/metrics', [OllamaServerController::class, 'metrics'])->name('servers.metrics');
    Route::get('servers/{server}/download-agent', [OllamaServerController::class, 'downloadAgent'])->name('servers.download-agent');
    Route::get('servers/{server}/test-pusher', [OllamaServerController::class, 'testPusherEvent'])->name('servers.test-pusher');
    
    // Test route to directly trigger Pusher events for monitoring
    Route::get('servers/{server}/trigger-monitor', function(\App\Models\OllamaServer $server) {
        $pusher = new \Pusher\Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options', [])
        );
        
        $channelName = 'server.' . $server->id;
        $eventName = 'App\\Events\\TestStatusUpdated';
        $data = [
            'id' => 999, // Fake test ID
            'status' => 'in_progress',
            'updated_at' => now()->toIso8601String(),
        ];
        
        $pusher->trigger($channelName, $eventName, $data);
        
        return response()->json([
            'success' => true,
            'message' => 'Manual monitor trigger sent directly via Pusher',
            'channel' => $channelName,
            'event' => $eventName,
            'data' => $data
        ]);
    })->name('servers.trigger-monitor');
    
    // Model test routes
    Route::prefix('servers/{server}')->name('tests.')->group(function () {
        // Compare tests route must come before the wildcard route
        Route::get('tests/compare', [OllamaModelTestController::class, 'compareResults'])->name('compare');
        Route::get('tests/compare/download-csv', [OllamaModelTestController::class, 'downloadComparisonCsv'])->name('compare.download-csv');
        Route::get('tests/create', [OllamaModelTestController::class, 'create'])->name('create');
        Route::post('tests', [OllamaModelTestController::class, 'store'])->name('store');
        Route::get('tests', [OllamaModelTestController::class, 'index'])->name('index');
        Route::get('tests/create-multiple', [OllamaModelTestController::class, 'createMultipleModels'])->name('create-multiple');
        Route::post('tests/multiple', [OllamaModelTestController::class, 'storeMultipleModels'])->name('store-multiple');
        Route::get('tests/{test}', [OllamaModelTestController::class, 'show'])->name('show');
        Route::delete('tests/{test}', [OllamaModelTestController::class, 'destroy'])->name('destroy');
    });
});

// Global redirect for any requests to /settings/profile
Route::get('settings/profile', function() {
    return redirect()->to(route('settings.all') . '#profile');
})->middleware(['auth']);

// Settings - Protected with auth middleware
Route::prefix('settings')->name('settings.')->middleware(['auth'])->group(function () {
    Route::get('/', [SettingsController::class, 'allSettings'])->name('index');
    Route::get('/broadcast', [SettingsController::class, 'showBroadcast'])->name('broadcast');
    Route::post('/broadcast', [SettingsController::class, 'updateBroadcast'])->name('broadcast.update');
    Route::get('/all', [SettingsController::class, 'allSettings'])->name('all');
    
    // Profile and password update routes
    Route::patch('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
    
    // Redirect any profile requests to our all-in-one settings page (now handled globally above)
    // Route::get('/profile', function() {
    //     return redirect()->route('settings.all')->fragment('profile');
    // });
});

Route::get('dashboard', function () {
    return redirect()->route('ollama.servers.index');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
