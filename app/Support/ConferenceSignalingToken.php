<?php

namespace App\Support;

use RuntimeException;

class ConferenceSignalingToken
{
    public static function issue(array $claims): string
    {
        $payload = $claims;
        $payload['iat'] = time();
        $payload['exp'] = time() + max(60, (int) config('conference_signaling.token_ttl', 7200));

        $encodedPayload = self::base64UrlEncode((string) json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = self::base64UrlEncode(hash_hmac('sha256', $encodedPayload, self::secret(), true));

        return $encodedPayload.'.'.$signature;
    }

    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $encodedSignature] = $parts;

        if ($encodedPayload === '' || $encodedSignature === '') {
            return null;
        }

        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $encodedPayload, self::secret(), true)
        );

        if (! hash_equals($expectedSignature, $encodedSignature)) {
            return null;
        }

        $payloadJson = self::base64UrlDecode($encodedPayload);
        if ($payloadJson === null) {
            return null;
        }

        $payload = json_decode($payloadJson, true);
        if (! is_array($payload)) {
            return null;
        }

        $expiresAt = (int) ($payload['exp'] ?? 0);
        if ($expiresAt < time()) {
            return null;
        }

        return $payload;
    }

    private static function secret(): string
    {
        $appKey = (string) config('app.key');

        if ($appKey === '') {
            throw new RuntimeException('APP_KEY is required for conference signaling tokens.');
        }

        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);

            if ($decoded !== false && $decoded !== '') {
                return $decoded;
            }
        }

        return $appKey;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): ?string
    {
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}
