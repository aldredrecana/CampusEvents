<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class QrToken
{
    public static function make(User $user): string
    {
        $payload = $user->id . '|' . (now()->timestamp + config('campus.qr_ttl_seconds')) . '|' . Str::random(12);
        $body = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        return $body . '.' . hash_hmac('sha256', $body, config('app.key'));
    }

    /** @return array{user_id:int, nonce:string}|null */
    public static function resolve(string $token): ?array
    {
        [$body, $signature] = array_pad(explode('.', $token, 2), 2, '');

        if (! hash_equals(hash_hmac('sha256', $body, config('app.key')), $signature)) {
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