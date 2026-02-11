<?php

/*
|--------------------------------------------------------------------------
| ZKTeco ICLOCK / ADMS Protocol Routes
|--------------------------------------------------------------------------
|
| These routes handle the ADMS push protocol at /iclock/* (NO /api prefix).
| ZKTeco devices in ADMS mode send HTTP requests directly to:
|   http://<server-domain>/iclock/cdata?SN=<serial_number>
|
| Registered in RouteServiceProvider with 'api' middleware (no CSRF).
| No authentication — devices identify via serial number in query string.
|
| Rate limiting: 120 requests/minute per IP (devices poll every ~10 seconds).
|
*/

use App\Http\Controllers\Api\ZKPushController;
use Illuminate\Support\Facades\Route;

Route::prefix('iclock')
    ->middleware('throttle:120,1') // 120 requests per minute per IP
    ->group(function () {

    // Base URL: status check
    Route::get('/', function () {
        return response("OK\r\n", 200)
            ->header('Content-Type', 'text/plain');
    });

    // Handshake: GET /iclock/cdata?SN=xxx — device checks in
    Route::get('/cdata', [ZKPushController::class, 'handshake']);

    // Data push: POST /iclock/cdata?SN=xxx — device sends attendance records
    Route::post('/cdata', [ZKPushController::class, 'receiveRecords']);

    // Command poll: GET /iclock/getrequest?SN=xxx — device asks for commands
    Route::get('/getrequest', [ZKPushController::class, 'getRequest']);

    // Command result: POST /iclock/devicecmd?SN=xxx — device reports command result
    Route::post('/devicecmd', [ZKPushController::class, 'deviceCmd']);
});
