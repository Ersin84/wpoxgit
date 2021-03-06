<?php

namespace Oxgit\Git;

class GitHubApiClient
{
    public function setUpWebhookForRepository($webhook, GitHubRepository $repository) {
        $token = get_option('gh_token');

        $payload = json_encode(array(
            'name' => 'web',
            'active' => true,
            'config' => array(
                'url' => html_entity_decode($webhook),
            ),
        ));

        $url = "https://api.github.com/repos/{$repository->__toString()}/hooks";

        $response = wp_remote_post($url, array(
            'body' => $payload,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => "token {$token}",
            ),
        ));

        if ($response instanceof \WP_Error) {
            throw new \Exception('Webhook was not updated on GitHub. Make sure a valid GitHub token is stored.');
        }
    }
}
