<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Api\ZKPushController;

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

Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| ZKTeco Push / ICLOCK / ADMS Protocol Endpoints
|--------------------------------------------------------------------------
| These endpoints receive real-time attendance data from ZKTeco devices
| configured in "push mode". The device sends attendance records directly
| to the server instead of waiting for the server to pull them.
|
| Device setup: Set server IP/domain and port in device network settings.
| URL format: http(s)://your-domain.com/api/iclock/cdata
|
| No authentication middleware — devices authenticate via serial number.
*/
Route::prefix('iclock')->group(function () {
    // Base URL: helpful status page
    Route::get('/', function () {
        return response('OK: ICLOCK server is running. Device endpoint: /api/iclock/cdata', 200)
            ->header('Content-Type', 'text/plain');
    });

    // Handshake: Device checks in and gets configuration
    Route::get('/cdata', [ZKPushController::class, 'handshake']);

    // Receive attendance records pushed by device
    Route::post('/cdata', [ZKPushController::class, 'receiveRecords']);

    // Device polls for pending commands
    Route::get('/getrequest', [ZKPushController::class, 'getRequest']);

    // Device pushes command results
    Route::post('/devicecmd', [ZKPushController::class, 'deviceCmd']);
});
