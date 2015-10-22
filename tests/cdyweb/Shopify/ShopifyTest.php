<?php

class ShopifyTest extends \PHPUnit_Framework_TestCase
{
    public function test_contruct() {
        $shopify = new \cdyweb\Shopify\Shopify('unit-test', array());
        $this->assertEquals($shopify, \cdyweb\Shopify\Shopify::getInstance());
    }

    public function test_getConnection() {
        $shopify = new \cdyweb\Shopify\Shopify(json_decode(json_encode(array('shopname'=>'unit-test','scope'=>array('read','write')))));
        $connection = $shopify->getConnection();
        $this->assertTrue($connection instanceof \ActiveResource\Connections\GuzzleConnection);
    }

}