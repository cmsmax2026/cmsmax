<?php

namespace App\Support\Security;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $length = 32): string
    {
        $alphabet = self::BASE32_ALPHABET;
        $maxIndex = strlen($alphabet) - 1;
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, $maxIndex)];
        }

        return $secret;
    }

    public static function verifyCode(string $secret, string $code, int $window = 1, int $timeSlice = null): bool
    {
        $secret = self::normalizeSecret($secret);
        $code = preg_replace('/\D+/', '', $code) ?: '';
        if ($secret === '' || strlen($code) !== 6) {
            return false;
        }

        $timeSlice = $timeSlice ?? self::getTimeSlice();
        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals(self::getCode($secret, $timeSlice + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function getCode(string $secret, int $timeSlice = null): string
    {
        $secretKey = self::base32Decode(self::normalizeSecret($secret));
        $timeSlice = $timeSlice ?? self::getTimeSlice();
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hm = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashPart = substr($hm, $offset, 4);
        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public static function buildProvisioningUri(string $issuer, string $accountName, string $secret): string
    {
        $issuer = trim($issuer);
        $accountName = trim($accountName);
        $label = rawurlencode($issuer !== '' ? ($issuer . ':' . $accountName) : $accountName);

        return 'otpauth://totp/' . $label
            . '?secret=' . rawurlencode(self::normalizeSecret($secret))
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=6&period=30';
    }

    public static function normalizeSecret(string $secret): string
    {
        return strtoupper(preg_replace('/[^A-Z2-7]/i', '', $secret) ?: '');
    }

    public static function getTimeSlice(): int
    {
        return (int) floor(time() / 30);
    }

    private static function base32Decode(string $secret): string
    {
        if ($secret === '') {
            return '';
        }

        $buffer = 0;
        $bitsLeft = 0;
        $output = '';
        $alphabet = array_flip(str_split(self::BASE32_ALPHABET));

        foreach (str_split($secret) as $char) {
            if (!array_key_exists($char, $alphabet)) {
                continue;
            }

            $buffer = ($buffer << 5) | $alphabet[$char];
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
