<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Hit 5 kali dan ukur waktunya
for ($i = 1; $i <= 5; $i++) {
    $request = Illuminate\Http\Request::create('/api/v1/routes', 'GET', ['is_active' => 'true']);
    
    $start = microtime(true);
    $response = $kernel->handle($request);
    $time = microtime(true) - $start;
    
    echo "Hit #$i: Time taken = " . round($time * 1000, 2) . " ms, Response Status = " . $response->getStatusCode() . "\n";
    $kernel->terminate($request, $response);
}
