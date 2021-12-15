<?php

namespace Nekoding\Tripay;

class Signature
{
    /**
     * @param string $data
     * @param string $signatureHash
     * @return bool
     */
    public static function validate(string $data, string $signatureHash): bool
    {
        $hashed = self::generate($data);

        return $hashed === $signatureHash;
    }

    /**
     * @param string $data
     * @return string
     */
    public static function generate(string $data): string
    {
        return hash_hmac(
            'sha256',
            $data,
            config('tripay.tripay_private_key')
        );
    }
}
