<?php

namespace Oxgit\WordPress;

use Plugin_Upgrader_Skin;
use Oxgit\Actions\PluginUpdateFailed;
use WP_Error;

class PluginUpgraderSkin extends Plugin_Upgrader_Skin
{
    /**
     * @var WP_Error
     */
    protected $error;
    protected $feedback;

    public function after()
    {
        // WP doesn't sent all errors as actual error objects
        if ($this->error === 'up_to_date') {
            $this->error = new WP_Error('wpoxgit_error', 'Package is up-to-date.');
        }

        // Optimise error messages
        if ($this->error and $this->error->get_error_code() === 'download_failed') {
            $errorMsg = '';

            if (isset($_POST['wpoxgit']['type']) && $_POST['wpoxgit']['type'] === 'gh') {
                $errorMsg .= '<p><strong>Common issues when using GitHub:</strong></p>';
                $errorMsg .= '<ul style="list-style: disc; padding-left: 1.2rem;"><li>The Git branch doesn\'t exist - GitHub now defaults to <code>main</code> instead of <code>master</code>.</li><li>Token has been invalidated, try obtaining <a href="admin.php?page=wpoxgit&tab=github">a new token</a>.</li><li>WP Oxgit doesn\'t have access to the GitHub org - grant it <a href="https://github.com/settings/connections/applications/c48c02cdb49a43bb36b8" target="_blank">here</a> and issue <a href="admin.php?page=wpoxgit&tab=github">a new token</a>.</li><li>Repository handle is incorrect.</li></ul>';
            }

            $this->error = new WP_Error('download_failed', $this->error->get_error_message() . $errorMsg);
        }

        // Probably because Bitbucket token has been invalidated
        if ($this->error and $this->error->get_error_code() === 'incompatible_archive') {
            $this->error = new WP_Error('incompatible_archive', $this->error->get_error_message() . ' If you are using Bitbucket, maybe your token has been invalidated. Try obtaining <a href="admin.php?page=wpoxgit&tab=bitbucket">a new one</a>.');
        }

        if ( ! is_null($this->error)) {
            do_action('wpoxgit_plugin_update_failed', new PluginUpdateFailed(
                $this->error->get_error_message()
            ));

            throw new InstallFailed($this->error->get_error_message());
        }
    }

    public function before()
    {
        // ...
    }

    public function error($error)
    {
        $this->error = $error;
    }

    public function header()
    {
        // ...
    }

    public function feedback($string, ...$args)
    {
        $this->feedback[$string] = true;
    }

    public function footer()
    {
        // ...
    }
}
