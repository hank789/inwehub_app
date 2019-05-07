<?php namespace App\Services\Hmac\Guards;

use App\Services\Hmac\Exceptions\SignatureVersionException;

class CheckVersion implements Guard
{

    /**
     * Check to ensure the auth parameters
     * satisfy the rule of the guard
     *
     * @param array  $auth
     * @param array  $signature
     * @param string $prefix
     * @throws SignatureVersionException
     * @return bool
     */
    public function check(array $auth, array $signature, $prefix)
    {
        if (! isset($auth[$prefix . 'version'])) {
            throw new SignatureVersionException('The version has not been set');
        }

        if ($auth[$prefix . 'version'] !== $signature[$prefix . 'version']) {
            throw new SignatureVersionException('The signature version is not correct');
        }

        return true;
    }
}
