<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\PostManagementInterface;

class PostManagement implements PostManagementInterface
{
    /**
     * {@inheritdoc}
     */
    public function customGetMethod($storeid, $name)
    {
        try {
            //code
            $response = [
                'storeid' => $storeid,
                'name' => $name,
                'type' => 'Hi Rajesh Bhai'
            ];
        } catch (\Exception $e) {
            $response = ['error' => $e->getMessage()];
        }

        return json_encode($response);
    }
    /**
     * {@inheritdoc}
     */
    public function customPostMethod($storeid, $name, $city)
    {
        try {
            $response = [
                'storeid' => $storeid,
                'name' => $name,
                'city' => $city
            ];
        } catch (\Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        return json_encode($response);
    }
}
