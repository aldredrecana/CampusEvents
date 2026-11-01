<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class QrToken
{
    public static function make(User $user): string
{
    $payload = $user->id . '|' . (now()->timestamp + config('campus.qr_ttl_seconds')) . '|' . Str::random(8);
    $body = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

    return $body . '.' . substr(hash_hmac('sha256', $body, config('app.key')), 0, 16);
}

    /** @return array{user_id:int, nonce:string}|null */
    public static function resolve(string $token): ?array
    {
        [$body, $signature] = array_pad(explode('.', $token, 2), 2, '');

$expected = substr(hash_hmac('sha256', $body, config('app.key')), 0, 16);

if (! hash_equals($expected, $signature)) {
    return null;
}

        $payload = base64_decode(strtr($body, '-_', '+/'));
        [$id, $expires, $nonce] = array_pad(explode('|', (string) $payload), 3, null);

        if (! $id || ! $expires || ! $nonce || now()->timestamp > (int) $expires) {
            return null;
        }

        return ['user_id' => (int) $id, 'nonce' => $nonce];
    }
}