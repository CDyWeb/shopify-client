<?php

if (file_exists('../vendor/autoload.php')) require '../vendor/autoload.php';
else if (file_exists('vendor/autoload.php')) require 'vendor/autoload.php';

use \cdyweb\Shopify\Shopify;

$config=array();
if (file_exists('config.json')) {
    $config=json_decode(file_get_contents('config.json'),true);
}
$client = new Shopify('cdyweb', $config);
$client->setTokenStorage(new \fkooman\OAuth\Client\SimpleStorage());

if (isset($_GET['code'])) {
    $client->callback();
    header('HTTP/1.1 302 Found');
    header('Location: '.$config['redirect_uri']);
    exit;
}

if (!$client->hasAccessToken()) {
    header('HTTP/1.1 302 Found');
    header('Location: '.$client->getAuthorizeUri());
    exit;
}

$pages = \cdyweb\Shopify\Model\Page::find('all');
foreach ($pages as $page) var_dump($page->getSchema()->getValues());

$products = \cdyweb\Shopify\Model\Product::find('all');
foreach ($products as $product) var_dump($product->getSchema()->getValues());
