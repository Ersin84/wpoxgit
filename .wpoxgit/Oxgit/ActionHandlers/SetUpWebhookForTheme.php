<?php

namespace Oxgit\ActionHandlers;

use Oxgit\Git\BitbucketApiClient;
use Oxgit\Git\BitbucketRepository;
use Oxgit\Git\GitHubApiClient;
use Oxgit\Git\GitHubRepository;

class SetUpWebhookForTheme
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
        $theme = $action->theme;

        $enablePushToDeploy = (bool) $theme->pushToDeploy;

        // Early return if Push-to-Deploy is not enabled
        if ( ! $enablePushToDeploy) {
            return null;
        }

        if ($theme->repository instanceof GitHubRepository) {
            $this->gitHub->setUpWebhookForRepository($theme->getPushToDeployUrl(), $theme->repository);
        }

        if ($theme->repository instanceof BitbucketRepository) {
            $this->bitbucket->setUpWebhookForRepository($theme->getPushToDeployUrl(), $theme->repository);
        }
    }
}
