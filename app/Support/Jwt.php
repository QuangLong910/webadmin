<?php

namespace App\Support;

/**
 * Đọc phần payload của JWT backend cấp (không kiểm chữ ký — việc đó backend làm).
 * Chỉ dùng để biết vai và hạn của token ở phía web.
 */
class Jwt
{
    private const ROLE_CLAIM = 'http://schemas.microsoft.com/ws/2008/06/identity/claims/role';

    public static function payload(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return [];
        }

        $json = base64_decode(strtr($parts[1], '-_', '+/'), true);

        return is_string($json) ? (json_decode($json, true) ?: []) : [];
    }

    /** Vai có thể là một chuỗi hoặc mảng tuỳ số vai của tài khoản. */
    public static function roles(string $token): array
    {
        $payload = self::payload($token);
        $roles = $payload[self::ROLE_CLAIM] ?? $payload['role'] ?? [];

        return (array) $roles;
    }

    public static function isExpired(string $token): bool
    {
        $exp = self::payload($token)['exp'] ?? null;

        return $exp !== null && $exp <= time();
    }
}
