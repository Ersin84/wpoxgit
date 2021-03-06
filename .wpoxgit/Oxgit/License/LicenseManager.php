<?php

namespace Oxgit\License;

class LicenseManager
{
    /**
     * @var LicenseApi
     */
    private $client;

    /**
     * @param LicenseApi $client
     */
    public function __construct(LicenseApi $client)
    {
        $this->client = $client;
    }

    public function licenseKey()
    {
        $key = get_option('wpoxgit_license_key', false);

        if ( ! $key) {
            return false;
        }

        $key = $this->client->getLicenseKey($key);

        return $key;
    }

    public function activateSiteLicense($key, $oldKey)
    {
        // Field is deactivated, this means we
        // want to revoke it, since it can't be activated twice.
        $deactivate = is_null($key);

        if ($deactivate) {
            return $this->client->removeLicenseFomSite($oldKey);
        }

        $isValid = $this->client->registerKeyForSite($key);

        if ( ! $isValid) {
            add_settings_error('invalid-license-key', '', 'WP Oxgit license could not be activated.');
        }

        return $isValid;
    }
}
