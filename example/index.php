<?php

require '../vendor/autoload.php';
require 'SimpleStorage.php';

use \cdyweb\Shopify\Client;

$config=array();
if (file_exists('config.json')) {
    $config=json_decode(file_get_contents('config.json'),true);
}
$client = new Client('cdyweb', $config);
$client->setTokenStorage(new SimpleStorage());

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

$pages = $client->getPages();
foreach ($pages as $page) var_dump($page->getSchema()->getValues());

$products = $client->getProducts();
foreach ($products as $product) var_dump($product->getSchema()->getValues());
