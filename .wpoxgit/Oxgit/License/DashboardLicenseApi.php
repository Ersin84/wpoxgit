<?php

namespace Oxgit\License;

use Oxgit\Log\Logger;

class DashboardLicenseApi implements LicenseApi
{
    private $baseUrl = 'https://dashboard.wpoxgit.com/api/license-keys/';

    /**
     * @var Logger
     */
    private $log;

    /**
     * @param Dashboard $dashboard
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->log = $log;
    }

    public function getLicenseKey($key)
    {
        if ( ! $key or $key === '') {
            return false;
        }

        $result = wp_remote_get($this->baseUrl . $key . '?site=' . home_url(), array('headers' => 'Accept: application/json'));

        if (is_wp_error($result)) {
            add_settings_error('invalid-license-server-message', '', 'We couldn\'t check your license. Are you connected to the Internet?');
        }

        $code = wp_remote_retrieve_response_code($result);

        if ($code !== 200) {
            $this->log->error("Error connecting to license server. Code: {$code}");
            return false;
        }

        $body = wp_remote_retrieve_body($result);

        $array = json_decode($body, true);

        if ( ! $array) {
            return false;
        }

        // Check if license is active for site
        // Check for license expiration
        if (! $array['activated_for_site'] and is_null($array['expires'])) {
            $key = $this->registerKeyForSite($key);

            if ( ! $key) {
                return false;
            }
        }

        // For backwards compatability:
        $array['token'] = $key;

        return LicenseKey::fromDashboardResponseArray($array);
    }

    public function registerKeyForSite($key)
    {
        // Try to register new license
        $body = array(
            'url' => home_url(),
        );
        $result = wp_remote_post($this->baseUrl . $key . '/activate', array(
            'headers' => array('Accept: application/json', 'Content-Type: application/json'),
            'body' => json_encode($body),
        ));

        $code = wp_remote_retrieve_response_code($result);

        if ($code === 201 || $code === 208) {
            return $key;
        }

        $this->log->error("Response from license server: " . wp_remote_retrieve_body( $result ));
        $this->log->error("Response code from license server: " . wp_remote_retrieve_response_code( $result ));

        // Error handling
        $body = wp_remote_retrieve_body($result);
        $array = json_decode($body, true);

        if ( ! $array) {
            return false;
        }

        if (isset($array['errors'])) {
            add_settings_error('invalid-license-key', '', implode(', ', $array['errors']));
            return false;
        }

        return $key;
    }

    public function removeLicenseFomSite($key)
    {
        $body = array(
            'url' => home_url(),
        );
        $result = wp_remote_post($this->baseUrl . $key . '/revoke', array(
            'headers' => array('Accept: application/json', 'Content-Type: application/json'),
            'body' => json_encode($body),
        ));

        $code = wp_remote_retrieve_response_code($result);

        if ($code !== 200) {
            add_settings_error('invalid-license-server-message', '', 'License could not be revoked from site. Please manually revoke it from the <a href="https://dashboard.wpoxgit.com">WP Oxgit Dashboard</a>.');
        }
    }
}
