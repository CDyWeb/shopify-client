<?php

//load the composer classes
require 'vendor/autoload.php';

//define the Active Record classes
class Shop extends \cdyweb\Shopify\Model\AbstractModel {};
class Page extends \cdyweb\Shopify\Model\AbstractModel {};
class Customer extends \cdyweb\Shopify\Model\AbstractModel {};

//load config
$config=array();
if (file_exists('config.json')) {
    $config=json_decode(file_get_contents('config.json'));
}

//connect to database
$pdo = new PDO('mysql:host=localhost;dbname=test', 'test', '');
//create a database token storage
$tokenStorage = new \cdyweb\Shopify\OAuth\PDOTokenStorage($pdo);
//create the Shopify API client
$client = new \cdyweb\Shopify\Shopify($config, $tokenStorage);

//if the request is a shopify callback, a temporary "code" is provided in the query string
if (isset($_GET['code'])) {
    //finalize the oauth process by requesting a permanent access token
    $client->authorizeCallback();
    //reload this page
    header('HTTP/1.1 302 Found');
    header('Location: '.$config['redirect_uri']);
    exit;
}

//if we haven't connected to Shopify yet, let's redirect to the Authorize page
if (!$client->hasAccessToken()) {
    header('HTTP/1.1 302 Found');
    header('Location: '.$client->getAuthorizeUri());
    exit;
}

//at this point, oauth is successfully set up, so we can start using the API
$pages = Page::find('all');
foreach ($pages as $page) var_dump($page->getSchema()->getValues());

$customers = Customer::find('all');
foreach ($customers as $customer) var_dump($customer->getSchema()->getValues());
