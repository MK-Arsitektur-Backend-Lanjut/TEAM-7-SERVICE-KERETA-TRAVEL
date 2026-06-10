<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class JwtService
{
    public function issueTokenForUser(User $user): array
    {
        $ttlMinutes = (int) (env('JWT_TTL', 60));
        if ($ttlMinutes <= 0) {
            $ttlMinutes = 60;
        }

        $now = time();
        $exp = $now + ($ttlMinutes * 60);

        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->getAuthIdentifier(),
            'iat' => $now,
            'exp' => $exp,
            'jti' => Str::uuid()->toString(),
        ];

        $token = $this->encode($payload);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $exp - $now,
        ];
    }

    public function decode(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = $this->jsonDecode($this->base64UrlDecode($encodedHeader));
        $payload = $this->jsonDecode($this->base64UrlDecode($encodedPayload));

        if (($header['alg'] ?? null) !== 'HS256') {
            throw new RuntimeException('Invalid token.');
        }

        $signingInput = $encodedHeader . '.' . $encodedPayload;
        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', $signingInput, $this->secret(), true));

        if (! hash_equals($expectedSignature, $encodedSignature)) {
            throw new RuntimeException('Invalid token.');
        }

        $now = time();
        if (isset($payload['exp']) && is_numeric($payload['exp']) && (int) $payload['exp'] < $now) {
            throw new RuntimeException('Token expired.');
        }

        return $payload;
    }

    private function encode(array $payload): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $encodedHeader = $this->base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedPayload = $this->base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_SLASHES));

        $signingInput = $encodedHeader . '.' . $encodedPayload;
        $signature = hash_hmac('sha256', $signingInput, $this->secret(), true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;
    }

    private function secret(): string
    {
        $secret = (string) env('JWT_SECRET', '');
        if ($secret !== '') {
            return $secret;
        }

        $appKey = (string) config('app.key');
        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        if ($appKey !== '') {
            return $appKey;
        }

        throw new RuntimeException('JWT secret not configured.');
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($decoded === false) {
            throw new RuntimeException('Invalid token.');
        }

        return $decoded;
    }

    private function jsonDecode(string $json): array
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid token.');
        }

        return $decoded;
    }
}
