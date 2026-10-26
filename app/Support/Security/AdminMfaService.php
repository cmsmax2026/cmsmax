<?php

namespace App\Support\Security;

use Illuminate\Support\Facades\Crypt;
use Modules\System\Models\AdminMfa;

class AdminMfaService
{
    public const MODE_OFF = 'off';
    public const MODE_OPTIONAL = 'optional';
    public const MODE_REQUIRED = 'required';

    public static function normalizeMode($mode): string
    {
        $mode = trim((string) $mode);
        if (in_array($mode, [self::MODE_OPTIONAL, self::MODE_REQUIRED], true)) {
            return $mode;
        }

        return self::MODE_OFF;
    }

    public static function getMode(): string
    {
        return self::normalizeMode(cacheGlobalSettingsByKey('admin_google_authenticator_mode'));
    }

    public static function isEnabledGlobally(): bool
    {
        return self::getMode() !== self::MODE_OFF;
    }

    public static function getEnabledRecord(int $uid): ?AdminMfa
    {
        if ($uid <= 0 || !self::hasTable()) {
            return null;
        }

        return AdminMfa::query()
            ->where('uid', $uid)
            ->where('status', 1)
            ->where('provider', 'google_authenticator')
            ->first();
    }

    public static function hasEnabledBinding(int $uid): bool
    {
        return self::getEnabledRecord($uid) !== null;
    }

    public static function shouldRequireSetup(int $uid): bool
    {
        return self::getMode() === self::MODE_REQUIRED && !self::hasEnabledBinding($uid);
    }

    public static function shouldRequireVerification(int $uid): bool
    {
        return self::getMode() !== self::MODE_OFF && self::hasEnabledBinding($uid);
    }

    public static function encryptSecret(string $secret): string
    {
        return Crypt::encryptString(TotpService::normalizeSecret($secret));
    }

    public static function decryptSecret(?string $payload): string
    {
        if ($payload === null || trim($payload) === '') {
            return '';
        }

        try {
            return TotpService::normalizeSecret(Crypt::decryptString($payload));
        } catch (\Throwable $exception) {
            return '';
        }
    }

    public static function verifyForUid(int $uid, string $code): bool
    {
        $record = self::getEnabledRecord($uid);
        if (!$record) {
            return false;
        }

        return TotpService::verifyCode(self::decryptSecret($record->secret), $code, 1);
    }

    public static function upsertSecret(int $uid, string $secret): AdminMfa
    {
        return AdminMfa::query()->updateOrCreate(
            [
                'uid' => $uid,
                'provider' => 'google_authenticator',
            ],
            [
                'secret' => self::encryptSecret($secret),
                'status' => 1,
                'bound_at' => now(),
                'last_verified_at' => now(),
            ]
        );
    }

    public static function disable(int $uid): void
    {
        if ($uid <= 0 || !self::hasTable()) {
            return;
        }

        AdminMfa::query()
            ->where('uid', $uid)
            ->where('provider', 'google_authenticator')
            ->update([
                'status' => 0,
                'updated_at' => now(),
            ]);
    }

    public static function touchVerified(int $uid): void
    {
        if ($uid <= 0 || !self::hasTable()) {
            return;
        }

        AdminMfa::query()
            ->where('uid', $uid)
            ->where('provider', 'google_authenticator')
            ->update([
                'last_verified_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private static function hasTable(): bool
    {
        return class_exists(AdminMfa::class)
            && \Illuminate\Support\Facades\Schema::hasTable(AdminMfa::TABLE_NAME);
    }
}
