<?php

namespace Oxgit\Storage;

use Oxgit\Git\Repository;
use Oxgit\Git\RepositoryFactory;
use Oxgit\Plugin;

class PluginRepository
{
    /**
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(RepositoryFactory $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
    }

    public function allOxgitPlugins()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        global $wpdb;

        $table_name = oxgitTableName();

        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE type = 1");

        $plugins = array();

        foreach ($rows as $row) {

            // This is our change to do some cleaning up
            if ( ! file_exists(WP_PLUGIN_DIR . "/" . $row->package)) {
                $this->delete($row->id);
                continue;
            }

            $array = get_plugin_data(WP_PLUGIN_DIR . "/" . $row->package);
            $plugins[$row->package] = Plugin::fromWpArray($row->package, $array);
            $repository = new Repository($row->repository);
            $repository->setBranch($row->branch);
            $plugins[$row->package]->setRepository($repository);
            $plugins[$row->package]->setPushToDeploy($row->ptd);
            $plugins[$row->package]->setHost($row->host);
            $plugins[$row->package]->setSubdirectory($row->subdirectory);
        }

        return $plugins;
    }

    public function delete($id)
    {
        global $wpdb;

        $table_name = oxgitTableName();

        $wpdb->delete($table_name, array('id' => sanitize_text_field($id)));
    }

    public function unlink($file)
    {
        global $wpdb;

        $table_name = oxgitTableName();

        $wpdb->delete($table_name, array('package' => sanitize_text_field($file)));
    }

    public function editPlugin($file, $input)
    {
        global $wpdb;

        $model = new PackageModel(array(
            'package' => $file,
            'repository' => (string) $input['repository'],
            'branch' => $input['branch'],
            'ptd' => $input['ptd'],
            'subdirectory' => $input['subdirectory'],
        ));

        $table_name = oxgitTableName();

        return $wpdb->update(
            $table_name,
            array(
                'repository' => $model->repository,
                'branch' => $model->branch,
                'ptd' => $model->ptd,
                'subdirectory' => $model->subdirectory,
            ),
            array('package' => $model->package)
        );
    }

    /**
     * @param $slug
     * @return Plugin
     */
    public function fromSlug($slug)
    {
        $plugins = get_plugins();

        foreach ($plugins as $file => $pluginInfo) {
            $tmp = explode('/', $file);
            $currentSlug = $tmp[0];

            if ($currentSlug === $slug) break;

            $file = null;
        }

        return oxgit()->make('Oxgit\Plugin')->fromWpArray($file, $pluginInfo);
    }

    /**
     * @param $file
     * @return Plugin $plugin
     * @throws PluginNotFound
     */
    public function oxgitPluginFromFile($file)
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        global $wpdb;

        $table_name = oxgitTableName();

        $model = new PackageModel(array('package' => $file));

        $row = $wpdb->get_row("SELECT * FROM $table_name WHERE type = 1 AND package = '{$model->package}'");

        if ( ! $row or ! file_exists(WP_PLUGIN_DIR . "/" . $row->package)) {
            throw new PluginNotFound('Could not find plugin.');
        }

        $array = get_plugin_data(WP_PLUGIN_DIR . "/" . $row->package);
        $plugin = oxgit()->make('Oxgit\Plugin')->fromWpArray($row->package, $array);

        $repository = $this->repositoryFactory->build(
            $row->host,
            $row->repository
        );

        $repository->setBranch($row->branch);
        $plugin->setRepository($repository);
        $plugin->setPushToDeploy($row->ptd);
        $plugin->setHost($row->host);
        $plugin->setSubdirectory($row->subdirectory);

        if ($row->private)
            $plugin->repository->makePrivate();

        return $plugin;
    }

    public function store(Plugin $plugin)
    {
        global $wpdb;

        $model = new PackageModel(array(
            'package' => $plugin->file,
            'repository' => (string) $plugin->repository,
            'branch' => $plugin->repository->getBranch(),
            'status' => 1,
            'host' => $plugin->repository->code,
            'private' => $plugin->repository->isPrivate(),
            'ptd' => $plugin->pushToDeploy,
            'subdirectory' => $plugin->getSubdirectory(),
        ));

        $table_name = oxgitTableName();

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE package = '$model->package'");

        if ($count !== '0') {

            return $wpdb->update(
                $table_name,
                array(
                    'branch' => $model->branch,
                    'status' => $model->status,
                    'subdirectory' => $model->subdirectory,
                ),
                array('package' => $model->package)
            );

        }

        return $wpdb->insert(
            $table_name,
            array(
                'package' => $model->package,
                'repository' => $model->repository,
                'branch' => $model->branch,
                'type' => 1,
                'status' => $model->status,
                'host' => $model->host,
                'private' => $model->private,
                'ptd' => $model->ptd,
                'subdirectory' => $model->subdirectory,
            )
        );
    }
}
