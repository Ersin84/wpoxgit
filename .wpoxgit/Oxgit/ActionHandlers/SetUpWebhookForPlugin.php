<?php

namespace Oxgit\ActionHandlers;

use Oxgit\Git\BitbucketApiClient;
use Oxgit\Git\BitbucketRepository;
use Oxgit\Git\GitHubApiClient;
use Oxgit\Git\GitHubRepository;

class SetUpWebhookForPlugin
{
    /**
     * @var BitbucketApiClient
     */
    public $bitbucket;

    /**
     * @var GitHubApiClient
     */
    private $gitHub;

    /**
     * @param BitbucketApiClient $bitbucket
     * @param GitHubApiClient $gitHub
     */
    public function __construct(BitbucketApiClient $bitbucket, GitHubApiClient $gitHub) {
        $this->bitbucket = $bitbucket;
        $this->gitHub = $gitHub;
    }

    public function handle($action) {
        $plugin = $action->plugin;

        $enablePushToDeploy = (bool) $plugin->pushToDeploy;

        // Early return if Push-to-Deploy is not enabled
        if ( ! $enablePushToDeploy) {
            return null;
        }

        if ($plugin->repository instanceof GitHubRepository) {
            $this->gitHub->setUpWebhookForRepository($plugin->getPushToDeployUrl(), $plugin->repository);
        }

        if ($plugin->repository instanceof BitbucketRepository) {
            $this->bitbucket->setUpWebhookForRepository($plugin->getPushToDeployUrl(), $plugin->repository);
        }
    }
}
