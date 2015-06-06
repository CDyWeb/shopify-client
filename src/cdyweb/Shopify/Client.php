<?php

namespace cdyweb\Shopify;

use ActiveResource\Connections\GuzzleConnection;
use fkooman\OAuth\Client\Callback;
use fkooman\OAuth\Client\Context;
use fkooman\OAuth\Client\SessionStorage;
use fkooman\OAuth\Client\ShopifyClientConfig;
use fkooman\OAuth\Client\Api;
use fkooman\OAuth\Client\StorageInterface;
use fkooman\OAuth\Client\AccessToken;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use \InvalidArgumentException;

class Client {

    public static $CLIENT_ID = 'shopify-client';

    /**
     * @var string
     */
    protected $shopName;

    /**
     * @var GuzzleConnection
     */
    protected $connection;

    /**
     * @var Api
     */
    protected $oauth;

    /**
     * @var ShopifyClientConfig
     */
    protected $clientConfig;

    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var Context
     */
    protected $context;


    /**
     * @param $shopName string
     */
    public function __construct($shopName, $clientConfig=null) {
        if (empty($shopName)) throw new InvalidArgumentException('shopName not provided');
        $this->shopName = $shopName;
        if ($clientConfig) {
            $this->setClientConfig($clientConfig);
        }
        if (is_array($clientConfig) && isset($clientConfig['scope'])) {
            $this->setContext(new Context($clientConfig['client_id'], $clientConfig['scope']));
        }
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
     * @param string $shopName
     */
    public function setShopName($shopName)
    {
        $this->shopName = $shopName;
    }

    private static function add_header(Client $that)
    {
        return function (callable $handler) use ($that) {
            return function (
                \GuzzleHttp\Psr7\Request $request,
                array $options
            ) use ($handler, $that) {
                $token = $that->getAccessToken();
                if ($token instanceof AccessToken) {
                    $request = $request
                        ->withHeader('X-Shopify-Access-Token', $token->getAccessToken())
                        ->withHeader('Accept', 'application/json');
                }
                return $handler($request, $options);
            };
        };
    }

    /**
     * @return \ActiveResource\Connections\GuzzleConnection
     */
    public function getConnection()
    {
        if (empty($this->connection)) {
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $stack->push(self::add_header($this));

            $guzzle = new \GuzzleHttp\Client([
                'verify'=>false,
                'handler' => $stack,
            ]);

            $this->connection = new GuzzleConnection("https://{$this->shopName}.myshopify.com");
            $this->connection->setClient($guzzle);
            $this->connection->setBasePath('/admin');
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
     * @return \fkooman\OAuth\Client\Api
     */
    public function getOauth()
    {
        if (empty($this->oauth)) {
            $connection = $this->getConnection();
            $this->oauth = new Api(self::$CLIENT_ID, $this->getClientConfig(), $this->getTokenStorage(), $connection->getClient());
        }
        return $this->oauth;
    }

    /**
     * @param \fkooman\OAuth\Client\Api $oauth
     */
    public function setOauth($oauth)
    {
        $this->oauth = $oauth;
    }

    /**
     * @return ShopifyClientConfig
     */
    public function getClientConfig()
    {
        if (empty($this->clientConfig)) {
            $this->clientConfig = new ShopifyClientConfig(['shopify'=>[]]);
        }
        return $this->clientConfig;
    }

    /**
     * @param ShopifyClientConfig|array $clientConfig
     */
    public function setClientConfig($clientConfig)
    {
        if ($clientConfig instanceof ShopifyClientConfig) {
            $this->clientConfig = $clientConfig;
        }
        if (is_array($clientConfig)) {
            $this->clientConfig = new ShopifyClientConfig(['shopify'=>$clientConfig]);
        }
    }

    /**
     * @return StorageInterface
     */
    public function getTokenStorage()
    {
        if (empty($this->tokenStorage)) {
            $this->tokenStorage = new SessionStorage();
        }
        return $this->tokenStorage;
    }

    /**
     * @param StorageInterface $tokenStorage
     */
    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        if (empty($this->context)) {
            $this->context = new Context(
                $this->getClientConfig()->getClientId(),
                $this->getClientConfig()->getDefaultServerScope()
            );
        }
        return $this->context;
    }

    /**
     * @param Context $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return bool|AccessToken
     * @throws \fkooman\OAuth\Client\Exception\ApiException
     */
    public function getAccessToken() {
        return $this->getOauth()->getAccessToken($this->getContext());
    }

    /**
     * @return bool
     */
    public function hasAccessToken() {
        return $this->getAccessToken() instanceof AccessToken;
    }

    /**
     * @return string
     * @throws \fkooman\OAuth\Client\Exception\ApiException
     */
    public function getAuthorizeUri() {
        return $this->getOauth()->getAuthorizeUri($this->getContext());
    }

    /**
     * @throws \fkooman\OAuth\Client\Exception\AuthorizeException
     * @throws \fkooman\OAuth\Client\Exception\CallbackException
     */
    public function callback() {
        $cb = new Callback(self::$CLIENT_ID, $this->getClientConfig(), $this->getTokenStorage(), $this->getConnection()->getClient());
        $cb->handleCallback($_GET);
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
