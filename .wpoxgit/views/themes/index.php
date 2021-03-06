<?php

// If this file is called directly, abort.
if ( ! defined('WPINC')) {
    die;
}

?><h2>WP Oxgit Themes</h2>

<hr>
<br>

<table class="wp-list-table widefat plugins">
	<thead>
    <tr>
        <th></th>
        <th scope="col" class="manage-column">Theme</th>
        <th scope="col" class="manage-column">Deploy Info</th>
        <th>Actions</th>
        <th></th>
        <th></th>
    </tr>
	</thead>

	<tbody id="the-list">
        <?php if (count($themes) < 1) { ?>
            <tr><td></td><td>No themes installed with WP Oxgit yet.</td></tr>
        <?php } ?>
        <?php foreach ($themes as $theme) { ?>
        <tr class="inactive" data-slug="akismet" data-plugin="akismet/akismet.php">
            <td></td>
            <td class="plugin-title column-primary"><strong><?php echo $theme->name; ?></strong><i class="fa <?php echo getHostIcon($theme->host); ?>"></i>&nbsp; <?php echo $theme->repository; ?></td>
            <td class="column-description desc" style="width: 100%;">
                <div class="inactive second plugin-version-author-uri">
                    Branch: <code class="wpoxgit-code"><?php echo $theme->repository->getBranch(); ?></code>
                    | Push-to-Deploy: <code class="wpoxgit-code <?php echo ($theme->pushToDeploy) ? 'wpoxgit-green' : 'wpoxgit-red'; ?>"><?php echo ($theme->pushToDeploy) ? 'enabled' : 'disabled'; ?></code>
                    <?php if ($theme->hasSubdirectory()) { ?>| Subdirectory: <code class="wpoxgit-code"><?php echo $theme->getSubdirectory(); ?></code><?php } ?>
                </div>
                <?php if ($theme->pushToDeploy) { ?>
                    <br>
                    <a href="#" class="wpoxgit-ptd-show">&raquo; Show Push-to-Deploy URL</a><div class="wpoxgit-ptd-url-container"><code class="ptd-url wpoxgit-code"><?php echo $theme->getPushToDeployUrl(); ?></code></div>
                <?php } ?>
            </td>
            <td>
                <a href="?page=wpoxgit-themes&package=<?php echo urlencode($theme->stylesheet); ?>" type="submit" class="button button-secondary"><i class="fa fa-wrench"></i>&nbsp; Edit theme</a>
            </td>
            <td>
                <form action="" method="POST">
                    <?php wp_nonce_field('update-theme'); ?>
                    <input type="hidden" name="wpoxgit[action]" value="update-theme">
                    <input type="hidden" name="wpoxgit[repository]" value="<?php echo $theme->repository; ?>">
                    <input type="hidden" name="wpoxgit[stylesheet]" value="<?php echo $theme->stylesheet; ?>">
                    <button type="submit" class="button button-primary button-update-package"><i class="fa fa-refresh"></i>&nbsp; Update theme</button>
                </form>
            </td>
            <td></td>
        </tr>
        <?php } ?>
	</tbody>
</table>
