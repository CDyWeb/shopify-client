<?php

namespace cdyweb\Shopify\OAuth;

class SimpleTokenStorage implements TokenStorage {

    /**
     * @param $shopName
     * @param $scope
     * @param $nonce
     */
    public function setNonce($shopName, $scope, $nonce)
    {
        $filename = md5($shopName.$scope).'.nonce';
        file_put_contents($filename, $nonce);
    }

    /**
     * @param $shopName
     * @param $scope
     * @return string
     */
    public function getNonce($shopName, $scope)
    {
        $filename = md5($shopName.$scope).'.nonce';
        if (file_exists($filename)) {
            return file_get_contents($filename);
        }
        return null;
    }

    /**
     * @param $shopName
     * @param $scope
     */
    public function clearNonce($shopName, $scope)
    {
        $filename = md5($shopName.$scope).'.nonce';
        unlink($filename);
    }

    /**
     * @param $shopName
     * @param $scope
     * @param $accessToken
     */
    public function setAccessToken($shopName, $scope, $accessToken)
    {
        $filename = md5($shopName.$scope).'.token';
        file_put_contents($filename, $accessToken);
    }

    /**
     * @param $shopName
     * @param $scope
     * @return string
     */
    public function getAccessToken($shopName, $scope)
    {
        $filename = md5($shopName.$scope).'.token';
        if (file_exists($filename)) {
            return file_get_contents($filename);
        }
        return null;
    }

}