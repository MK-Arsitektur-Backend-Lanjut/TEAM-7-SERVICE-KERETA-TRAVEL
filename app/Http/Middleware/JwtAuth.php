<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class JwtAuth
{
    public function __construct(private readonly JwtService $jwtService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractBearerToken($request);
        if ($token === null) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $payload = $this->jwtService->decode($token);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId = $payload['sub'] ?? null;
        if (! is_numeric($userId)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::find((int) $userId);
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Auth::guard()->setUser($user);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }

    private function extractBearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if (! is_string($header) || $header === '') {
            return null;
        }

        if (preg_match('/^Bearer\s+(.*)$/i', $header, $matches) !== 1) {
            return null;
        }

        $token = trim($matches[1]);

        return $token !== '' ? $token : null;
    }
}
