<?php

namespace Oxgit\Services;

class TokenGenerator
{
    public function addTokenOption()
    {
        return add_option('wpoxgit_token', $this->generateToken());
    }

    public function refreshTokenFilter($newValue, $oldValue)
    {
        return $this->generateToken();
    }

    protected function generateToken()
    {
        // Fallback if PHP is compiled without openssl
        if ( ! function_exists('openssl_random_pseudo_bytes'))
        {
            return md5(time()) . md5(time());
        }

        return bin2hex(openssl_random_pseudo_bytes(32));
    }
}
