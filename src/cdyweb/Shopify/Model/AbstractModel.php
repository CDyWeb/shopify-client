<?php
/**
 * Created by PhpStorm.
 * User: Erwin
 * Date: 15/06/2015
 * Time: 12:14 PM
 */

namespace cdyweb\Shopify\Model;


use ActiveResource\Base;
use cdyweb\Shopify\Shopify;

class AbstractModel extends Base {

    /**
     * @override
     * @return \ActiveResource\Connections\Connection
     */
    public static function getDefaultConnection()
    {
        if (null === self::$default_connection) {
            $shopify = Shopify::getInstance();
            if (!$shopify->hasAccessToken()) {
                throw new \InvalidArgumentException('access token missing');
            }
            self::$default_connection = $shopify->getConnection();
        }
        return self::$default_connection;
    }

}