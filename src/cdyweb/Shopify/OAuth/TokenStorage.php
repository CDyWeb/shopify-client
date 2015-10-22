<?php

namespace cdyweb\Shopify\OAuth;

interface TokenStorage {

    /**
     * @param $shopName
     * @param $scope
     * @param $nonce
     */
    public function setNonce($shopName, $scope, $nonce);

    /**
     * @param $shopName
     * @param $scope
     * @return string
     */
    public function getNonce($shopName, $scope);

    /**
     * @param $shopName
     * @param $scope
     */
    public function clearNonce($shopName, $scope);

    /**
     * @param $accessToken
     */
    public function setAccessToken($shopName, $scope, $accessToken);

    /**
     * @return string
     */
    public function getAccessToken($shopName, $scope);


}