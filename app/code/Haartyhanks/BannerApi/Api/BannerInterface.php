<?php
namespace Haartyhanks\BannerApi\Api;

interface BannerInterface
{
    /**
     * Return banner JSON data from CMS Block
     *
     * @return array
     */
    public function getBanners();
}
