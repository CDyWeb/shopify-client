<?php

class PDOTokenStorageTest extends \PHPUnit_Framework_TestCase {

    /** @var  \PDO */
    private $pdo;
    /** @var  \cdyweb\Shopify\OAuth\PDOTokenStorage */
    private $st;

    public function setUp() {
        $this->pdo = new PDO('mysql:host=localhost;dbname=test', 'test', '');
        $this->st = new \cdyweb\Shopify\OAuth\PDOTokenStorage($this->pdo);
    }

    public function test_setNonce() {
        $result = $this->st->setNonce('unit-test','read,write','my_nonce_1234');
        $this->assertNull($result);
    }

    public function test_getNonce() {
        $this->st->setNonce('unit-test','read,write','my_nonce_1234');
        $result = $this->st->getNonce('unit-test','read,write');
        $this->assertEquals('my_nonce_1234', $result);
    }

    public function test_setAccessToken() {
        $result = $this->st->setAccessToken('unit-test','read,write','my_token_1234');
        $this->assertNull($result);
    }

    public function test_getAccessToken() {
        $this->st->setAccessToken('unit-test','read,write','my_token_1234');
        $result = $this->st->getAccessToken('unit-test','read,write');
        $this->assertEquals('my_token_1234', $result);
    }

}