<?php

namespace Mw\Extension\Api;

interface PostManagementInterface
{
    /**
     * GET for Post api
     * @param string $storeid
     * @param string $name
     * @return string
     */
    public function customGetMethod($storeid, $name);
    /**
     * GET for Post api
     * @param string $storeid
     * @param string $name
     * @param string $city
     * @return string
     */
    public function customPostMethod($storeid, $name, $city);
}
