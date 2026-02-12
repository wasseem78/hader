<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;

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
| ICLOCK routes are registered in routes/iclock.php (without /api prefix)
| and loaded by RouteServiceProvider with 'api' middleware.
| Device URL format: http(s)://your-domain.com/iclock/cdata
|
| The duplicate /api/iclock/* routes have been removed to avoid confusion.
| Devices should be configured to use /iclock/cdata (not /api/iclock/cdata).
*/
