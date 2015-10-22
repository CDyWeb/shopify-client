<?php

use \cdyweb\Shopify\OAuth\OAuth2;

class OAuth2Test extends \PHPUnit_Framework_TestCase
{

    public function test_construct() {
        $auth = new OAuth2(json_decode(json_encode(array('shopname'=>'unit-test','scope'=>array('read','write')))));
        $this->assertTrue($auth instanceof OAuth2);
    }

    public function test_getAccessToken() {
        $auth = new OAuth2(json_decode(json_encode(array('shopname'=>'unit-test','scope'=>array('read','write')))));
        $st = $this->getMock('\cdyweb\Shopify\OAuth\TokenStorage');
        #$auth->getTokenStorage()->setAccessToken('unit-test', 'read,write', 'abc');
        $st->expects($this->once())->method('getAccessToken')->willReturn('abc');
        $auth->setTokenStorage($st);
        $token = $auth->getAccessToken();
        $this->assertEquals('abc', $token);
    }

    public function test_getAuthorizeUri() {
        $auth = new OAuth2(json_decode(json_encode(array(
            'shopname'=>'unit-test',
            'scope'=>array('read','write'),
            'client_id'=>'foo',
            'client_secret'=>'bar',
            'redirect_uri'=>'http://baz',
        ))));

        $st = $this->getMock('\cdyweb\Shopify\OAuth\TokenStorage');
        $st->expects($this->once())->method('setNonce');
        $auth->setTokenStorage($st);

        $uri = $auth->getAuthorizeUri();
        $pattern = '#^'.preg_quote(
                'https://unit-test.myshopify.com/admin/oauth/authorize?'.http_build_query(array(
                    'client_id'=>'foo',
                    'scope'=>'read,write',
                    'redirect_uri'=>'http://baz'
                )).'&state='
            ).'\w+$#';
        $this->assertEquals(1, preg_match($pattern, $uri));
    }

    public function test_valideSignature() {
        $auth = new OAuth2(json_decode(json_encode(array(
            'shopname'=>'unit-test',
            'scope'=>array('read','write'),
            'client_id'=>'foo',
            'client_secret'=>'!@#$%^&*()',
            'redirect_uri'=>'http://baz',
        ))));

        $params = array(
            'code'=>'1234abcd',
            'timestamp'=>'123456789',
            'state'=>'987654321',
            'shop'=>'unit-test.myshopify.com',
        );
        $copy = array_merge(array(), $params);
        ksort($copy);
        $str = http_build_query($copy);
        $params['hmac'] = hash_hmac('sha256', $str, '!@#$%^&*()');

        $result = $auth->valideSignature($params);
        $this->assertTrue($result);
    }

    public function test_callback() {
        $auth = new OAuth2(json_decode(json_encode(array(
            'shopname'=>'unit-test',
            'scope'=>array('read','write'),
            'client_id'=>'foo',
            'client_secret'=>'!@#$%^&*()',
            'redirect_uri'=>'http://baz',
        ))));

        $params = array(
            'code'=>'1234abcd',
            'timestamp'=>'123456789',
            'state'=>'987654321',
            'shop'=>'unit-test.myshopify.com',
        );
        $copy = array_merge(array(), $params);
        ksort($copy);
        $str = http_build_query($copy);
        $params['hmac'] = hash_hmac('sha256', $str, '!@#$%^&*()');

        $http = $this->getMock('\cdyweb\http\Adapter');
        $http->expects($this->once())->method('post')->willReturn(new \cdyweb\http\psr\Response(200,array(),json_encode(array('access_token'=>'qwerty'))));

        $st = $this->getMock('\cdyweb\Shopify\OAuth\TokenStorage');
        $st->expects($this->once())->method('getNonce')->willReturn('987654321');
        $st->expects($this->once())->method('setAccessToken');
        $auth->setTokenStorage($st);

        $result = $auth->callback($http, $params);
        $this->assertNull($result);
    }

}