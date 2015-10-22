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

    /** @var GuzzleConnection */
    protected $connection;

    /** @var OAuth2 */
    protected $auth;

    /** @var string */
    protected $shopName;

    /**
     * @return Shopify
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * @param \stdClass $clientConfig
     * @param TokenStorage $tokenStorage
     */
    public function __construct(\stdClass $clientConfig, TokenStorage $tokenStorage=null) {
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
    public function getShopName() {
        return $this->shopName;
    }

    /**
     * @return \ActiveResource\Connections\GuzzleConnection
     */
    public function getConnection() {
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
    public function setConnection($connection) {
        $this->connection = $connection;
    }

    /**
     * @return OAuth2
     */
    public function getAuth() {
        return $this->auth;
    }

    /**
     * Do we have an OAuth access token?
     * @return bool
     */
    public function hasAccessToken() {
        $accessToken = $this->getAuth()->getAccessToken();
        return !empty($accessToken);
    }

    /**
     * OAUTH request
     * @return string
     */
    public function getAuthorizeUri() {
        return $this->getAuth()->getAuthorizeUri();
    }

    /**
     * OAUTH callback
     */
    public function authorizeCallback() {
        $this->getAuth()->callback($this->getConnection()->getClient(), $_GET);
    }

    /**
     * @param $str string ($str = file_get_contents('php://input'))
     * @param $params array ($_SERVER)
     * @return bool
     */
    public function validateWebhook($str, $params=null) {
        if (empty($params)) $params = $_SERVER;
        $hmac_header = $params['HTTP_X_SHOPIFY_HMAC_SHA256'];
        $calculated_hmac = hash_hmac('sha256', $str, $this->getAuth()->getClientConfig()->client_secret);
        return ($hmac_header == $calculated_hmac);
    }



# File actionpack/lib/action_controller/vendor/rack-1.0/rack/utils.rb, line 32
#def parse_query(qs, d = '&;')
#params = {}
#(qs || '').split(/[#{d}] */n).each do |p|
#    k, v = unescape(p).split('=', 2)
#        if cur = params[k]
#          if cur.class == Array
#            params[k] << v
#          else
#            params[k] = [cur, v]
#          end
#        else
#          params[k] = v
#        end
#      end
#      return params
#    end

    /**
     * PHP port of Ruby Rack::Utils.parse_query
     * @param $qs
     * @param string $d
     * @return array
     */
    public function parse_query($qs, $d = '&') {
        $params=array();
        $arr = explode($d,$qs);
        foreach ($arr as $p) {
            list($k,$v) = explode('=',urldecode($p),2);
            if (isset($params[$k])) {
                if (is_array($params[$k])) {
                    $params[$k][] = $v;
                } else {
                    $params[$k] = array($params[$k], $v);
                }
            } else {
                $params[$k] = $v;
            }
        }
        return $params;
    }

    /**
     * @param $query_string
     * @return bool
     */
    public function validateProxy($query_string) {
        $query = $this->parse_query($query_string);
        $signature = $query['signature'];
        unset($query['signature']);
        ksort($query);
        $str = '';
        foreach ($query as $k=>$v) {
            if (is_array($v)) $str.="{$k}=".implode(',',$v);
            else $str.="{$k}={$v}";
        }
        $calculated_hmac = hash_hmac('sha256', $str, $this->getAuth()->getClientConfig()->client_secret);
        return ($signature == $calculated_hmac);
    }

    /**
     * Demo function, this is how you get all Pages
     * @return array
     */
    public function getPages() {
        if (!$this->hasAccessToken()) {
            throw new \InvalidArgumentException('access token missing');
        }
        return Model\Page::find('all', $this->getConnection());
    }

    /**
     * Demo function, this is how you get all Products (or at least the first 50)
     * @return array
     */
    public function getProducts() {
        if (!$this->hasAccessToken()) {
            throw new \InvalidArgumentException('access token missing');
        }
        return Model\Product::find('all', $this->getConnection());
    }

}
