<?php

namespace cdyweb\Shopify;

use ActiveResource\Connections\GuzzleConnection;
use cdyweb\http\guzzle\Guzzle;
use cdyweb\Shopify\OAuth\OAuth2;
use cdyweb\Shopify\OAuth\TokenStorage;
use \InvalidArgumentException;
use \RuntimeException;

class Shopify {

    /**
     * @var Shopify
     */
    protected static $instance = null;

    /**
     * @var string
     */
    public static $CLIENT_ID = 'shopify-client';

    /**
     * @var GuzzleConnection
     */
    protected $connection;

    /**
     * @var OAuth2
     */
    protected $auth;

    /**
     * @var string
     */
    protected $shopName;

    /**
     * @return Shopify
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param $shopName string
     */
    public function __construct($clientConfig, $tokenStorage=null) {
        if (empty($clientConfig)) throw new InvalidArgumentException('clientConfig not provided');
        if (empty($clientConfig->shopname)) throw new InvalidArgumentException('shopname not provided');
        if (empty($clientConfig->scope)) throw new InvalidArgumentException('scope not provided');
        $this->shopName = $clientConfig->shopname;
        $this->auth = new OAuth2($clientConfig, $tokenStorage);
        self::$instance = $this;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopName()
    {
        return $this->shopName;
    }

    /**
     * @return \ActiveResource\Connections\GuzzleConnection
     */
    public function getConnection()
    {
        if (empty($this->connection)) {
            $adapter = Guzzle::getAdapter();

            $this->connection = new GuzzleConnection("https://{$this->shopName}.myshopify.com");
            $this->connection->setClient($adapter);
            $this->connection->setBasePath('/admin');

            $accessToken = $this->getAuth()->getAccessToken();
            if ($accessToken) {
                $this->connection->getClient()->appendRequestHeader('X-Shopify-Access-Token', $accessToken);
            }
        }
        return $this->connection;
    }

    /**
     * @param \ActiveResource\Connections\GuzzleConnection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return OAuth2
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param OAuth2 $auth
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->getAuth()->getTokenStorage();
    }

    /**
     * @param TokenStorage $tokenStorage
     */
    public function setTokenStorage(TokenStorage $tokenStorage)
    {
        $this->getAuth()->setTokenStorage($tokenStorage);
    }

    /**
     * @return bool
     */
    public function hasAccessToken() {
        $accessToken = $this->getAuth()->getAccessToken();
        return !empty($accessToken);
    }

    /**
     * @return string
     */
    public function getAuthorizeUri() {
        return $this->getAuth()->getAuthorizeUri();
    }

    public function authorizeCallback() {
        $this->getAuth()->callback($this->connection->getClient(), $_GET);
    }

    public function getPages() {
        if (!$this->hasAccessToken()) {
            throw new \InvalidArgumentException('access token missing');
        }
        return Model\Page::find('all', $this->getConnection());
    }

    public function getProducts() {
        if (!$this->hasAccessToken()) {
            throw new \InvalidArgumentException('access token missing');
        }
        return Model\Product::find('all', $this->getConnection());
    }

}
