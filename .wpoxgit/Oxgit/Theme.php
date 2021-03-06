<?php

namespace Oxgit;

use Oxgit\Git\Repository;
use WP_Theme;

class Theme implements Package
{
    protected $stylesheet;
    protected $name;
    protected $themeURI;
    protected $description;
    protected $author;
    protected $authorURI;
    protected $version;
    protected $template;
    protected $status;
    protected $tags;
    protected $textDomain;
    protected $domainPath;
    protected $oxgitStatus;
    protected $pushToDeploy;
    protected $host;
    protected $subdirectory;

    public static function fromWpThemeObject(WP_Theme $object)
    {
        $theme = new static();

        $theme->stylesheet = $object->get_stylesheet();
        $theme->name = $object['Name'];
        $theme->themeURI = $object['PluginURI'];
        $theme->description = $object['Description'];
        $theme->author = $object['Author'];
        $theme->authorURI = $object['AuthorURI'];
        $theme->version = $object['Version'];
        $theme->template = $object['Template'];
        $theme->status = $object['status'];
        $theme->tags = $object['tags'];
        $theme->textDomain = $object['TextDomain'];
        $theme->domainPath = $object['DomainPath'];

        return $theme;
    }

    public function getSlug()
    {
        if ( ! $this->hasSubdirectory()) {
            return $this->repository->getSlug();
        }

        $parts = explode('/', $this->getSubdirectory());

        return end($parts);
    }

    public function getSubdirectory()
    {
        return $this->subdirectory;
    }

    public function hasSubdirectory()
    {
        return ! (is_null($this->getSubdirectory()) or $this->getSubdirectory() === '');
    }

    public function setSubdirectory($subdirectory)
    {
        $this->subdirectory = $subdirectory;
    }

    public function setOxgitStatus($oxgitStatus)
    {
        $this->oxgitStatus = $oxgitStatus;
    }

    public function setPushToDeploy($pushToDeploy)
    {
        $this->pushToDeploy = $pushToDeploy;
    }

    public function getPushToDeployUrl()
    {
        $package = base64_encode($this->stylesheet);

        $url = sprintf("%s?wpoxgit-hook&token=%s&package=%s",
            trailingslashit(get_site_url()),
            get_option('wpoxgit_token'),
            $package
        );

        return esc_attr($url);
    }

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function __get($name)
    {
        $method = "get" . ucfirst($name);

        if (method_exists($this, $method))
        {
            return $this->$method();
        }

        if (isset($this->$name))
        {
            return $this->$name;
        }
    }

    public function __toString()
    {
        return $this->stylesheet;
    }

    public function getIdentifier()
    {
        return $this->stylesheet;
    }
}
