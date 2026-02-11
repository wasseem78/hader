<?php

use Illuminate\Support\Facades\Http;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/register', 'POST', [
    'company_name' => 'Test Corp ' . time(),
    'subdomain' => 'test' . time(),
    'name' => 'Test User',
    'email' => 'test' . time() . '@example.com',
    'password' => 'password',
    'password_confirmation' => 'password',
    '_token' => 'testing',
]);

// Set Referer to simulate coming from the register page
$request->headers->set('Referer', 'http://localhost:8000/register');

$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Location: " . $response->headers->get('Location') . "\n";

// If validation failed, the session will have errors. 
// In a raw script, the session might not be persisted to file unless we terminate.
$kernel->terminate($request, $response);

// Now we can try to read the session if we knew the ID, but let's just look at Location.
if ($response->getStatusCode() == 302) {
    if ($response->headers->get('Location') == 'http://localhost:8000/register') {
        echo "Result: VALIDATION FAILURE (Redirected back to form)\n";
    } else {
        echo "Result: SUCCESS (Redirected to " . $response->headers->get('Location') . ")\n";
    }
}
