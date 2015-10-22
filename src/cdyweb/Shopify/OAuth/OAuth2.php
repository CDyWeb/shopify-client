<?php

namespace cdyweb\Shopify\OAuth;

class OAuth2 {

    /**
     * @var \stdClass
     */
    protected $clientConfig;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    function __construct($clientConfig=null, $tokenStorage=null)
    {
        if (!empty($clientConfig)) $this->setClientConfig($clientConfig);
        if (!empty($tokenStorage)) $this->setTokenStorage($tokenStorage);
    }

    /**
     * @return \stdClass
     */
    public function getClientConfig()
    {
        return $this->clientConfig;
    }

    /**
     * @param \stdClass $clientConfig
     */
    public function setClientConfig($clientConfig)
    {
        if (empty($clientConfig->shopname)) {
            throw new \InvalidArgumentException('Shopname not defined');
        }
        $this->clientConfig = $clientConfig;
    }

    /**
     * @return TokenStorage
     */
    public function getTokenStorage()
    {
        if (empty($this->tokenStorage)) {
            $this->tokenStorage = new SimpleTokenStorage();
        }
        return $this->tokenStorage;
    }

    /**
     * @param TokenStorage $tokenStorage
     */
    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getScope() {
        return implode(',',$this->clientConfig->scope);
    }

    /**
     * @return string
     */
    public function getAccessToken() {
        if (empty($this->clientConfig)) {
            throw new \RuntimeException('No config');
        }
        return $this->getTokenStorage()->getAccessToken($this->clientConfig->shopname, $this->getScope());
    }

    public function getAuthorizeUri() {
        if (empty($this->clientConfig)) {
            throw new \RuntimeException('No config');
        }
        $nonce = sha1(uniqid(time(), true));
        $this->getTokenStorage()->setNonce($this->clientConfig->shopname, $this->getScope(), $nonce);
        return 'https://'.$this->clientConfig->shopname.'.myshopify.com/admin/oauth/authorize?'.http_build_query(array(
            'client_id'=>$this->clientConfig->client_id,
            'scope'=>$this->getScope(),
            'redirect_uri'=>$this->clientConfig->redirect_uri,
            'state'=>$nonce,
        ));
    }

    public function valideSignature($params) {
        $hmac = $params['hmac'];
        unset($params['hmac']);
        unset($params['signature']);
        ksort($params);
        $str = http_build_query($params);
        $calculated = hash_hmac('sha256', $str, $this->clientConfig->client_secret);
        return strcmp($hmac, $calculated) == 0;
    }

    public function callback(\cdyweb\http\Adapter $http, $params) {
        if (empty($this->clientConfig)) {
            throw new \RuntimeException('No config');
        }
        $nonce = $this->getTokenStorage()->getNonce($this->clientConfig->shopname, $this->getScope());
        if (empty($params['code'])) {
            throw new \RuntimeException('Auth Code not provided');
        }
        if (empty($params['state']) || ($params['state']!=$nonce)) {
            throw new \RuntimeException('State (nonce) mismatch, received:'.@$params['state'].', expected:'.$nonce);
        }
        if (empty($params['hmac']) || !$this->valideSignature($params)) {
            throw new \RuntimeException('Signature (hmac) failed');
        }
        $response = $http->post(
            'https://'.$this->clientConfig->shopname.'.myshopify.com/admin/oauth/access_token',
            array(
                'Accept'=>'application/json'
            ),
            array(
                'redirect_uri'=>$this->clientConfig->redirect_uri,
                'client_id'=>$this->clientConfig->client_id,
                'client_secret'=>$this->clientConfig->client_secret,
                'code'=>$params['code'],
                'grant_type' => 'authorization_code'
            )
        );
        $json = json_decode($response->getBody()->getContents(), true);
        if (!isset($json['access_token'])) {
            throw new \RuntimeException('No access_token received');
        }
        $this->getTokenStorage()->clearNonce($this->clientConfig->shopname, $this->getScope());
        $this->getTokenStorage()->setAccessToken($this->clientConfig->shopname, $this->getScope(), $json['access_token']);
    }

}