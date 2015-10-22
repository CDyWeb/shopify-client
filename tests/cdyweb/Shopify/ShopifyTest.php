<?php

class ShopifyTest extends \PHPUnit_Framework_TestCase
{
    public function test_contruct() {
        $shopify = new \cdyweb\Shopify\Shopify(json_decode(json_encode(array('shopname'=>'unit-test','scope'=>array('read','write')))));
        $this->assertEquals($shopify, \cdyweb\Shopify\Shopify::getInstance());
    }

    public function test_getConnection() {
        $shopify = new \cdyweb\Shopify\Shopify(json_decode(json_encode(array('shopname'=>'unit-test','scope'=>array('read','write')))));
        $connection = $shopify->getConnection();
        $this->assertTrue($connection instanceof \ActiveResource\Connections\GuzzleConnection);
    }

    public function test_validateWebhook() {
        $shopify = new \cdyweb\Shopify\Shopify(json_decode(json_encode(array(
            'shopname'=>'unit-test',
            'scope'=>array('read','write'),
            'client_id'=>'foo',
            'client_secret'=>'bar',
            'redirect_uri'=>'http://baz',
        ))));
        $params = array('HTTP_X_SHOPIFY_HMAC_SHA256'=>'482eb81de7565456e88e7fe734b0b17a40cc5d457d291c6e878eddb2f324d7ce');
        $str = 'Lorem Ipsum';
        $result = $shopify->validateWebhook($str,$params);
        $this->assertEquals(true, $result);
    }

    public function test_validateProxy() {
        $shopify = new \cdyweb\Shopify\Shopify(json_decode(json_encode(array(
            'shopname'=>'unit-test',
            'scope'=>array('read','write'),
            'client_id'=>'foo',
            'client_secret'=>'hush',
            'redirect_uri'=>'http://baz',
        ))));
        $query_string = "extra=1&extra=2&shop=shop-name.myshopify.com&path_prefix=%2Fapps%2Fawesome_reviews&timestamp=1317327555&signature=a9718877bea71c2484f91608a7eaea1532bdf71f5c56825065fa4ccabe549ef3";
        $result = $shopify->validateProxy($query_string);
        $this->assertEquals(true, $result);
    }

}