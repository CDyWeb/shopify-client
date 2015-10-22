<?php

namespace cdyweb\Shopify\OAuth;

class PDOTokenStorage implements TokenStorage {

    /** @var \PDO */
    protected $pdo;

    /** @var string */
    protected $tableName;

    public function __construct(\PDO $pdo, $prefix='shopify_') {
        $this->pdo = $pdo;
        $this->tableName = $prefix.'auth_token';
        $this->checkDatabase();
    }

    public function checkDatabase() {
        $arr=array();
        if ($st = $this->pdo->query("describe `{$this->tableName}`")) {
            $arr = $st->fetchAll(\PDO::FETCH_ASSOC);
        }
        if (count($arr)==5) return;

        $this->pdo->exec("DROP TABLE IF EXISTS `{$this->tableName}`");
        $this->pdo->exec("
CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
  `shopname` varchar(255) DEFAULT NULL,
  `scope` varchar(255) DEFAULT NULL,
  `nonce` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`shopname`,`scope`)
)");
    }

    /**
     * @param $shopName
     * @param $scope
     * @param $nonce
     */
    public function setNonce($shopName, $scope, $nonce)
    {
        $this->pdo
            ->prepare("replace into `{$this->tableName}` set `shopname`=:shopname, `scope`=:scope, `nonce`=:nonce")
            ->execute(array(':shopname'=>$shopName, ':scope'=>$scope, ':nonce'=>$nonce));
    }

    /**
     * @param $shopName
     * @param $scope
     * @return string
     */
    public function getNonce($shopName, $scope)
    {
        $st = $this->pdo->prepare("select `nonce` from `{$this->tableName}` where `shopname`=:shopname and `scope`=:scope");
        $st->execute(array(':shopname'=>$shopName, ':scope'=>$scope));
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row) return $row['nonce'];
        return null;
    }

    /**
     * @param $shopName
     * @param $scope
     */
    public function clearNonce($shopName, $scope)
    {
        $this->pdo
            ->prepare("update `{$this->tableName}` set `nonce`=null where `shopname`=:shopname and `scope`=:scope")
            ->execute(array(':shopname'=>$shopName, ':scope'=>$scope));
    }

    /**
     * @param $shopName
     * @param $scope
     * @param $accessToken
     */
    public function setAccessToken($shopName, $scope, $token)
    {
        $this->pdo
            ->prepare("replace into `{$this->tableName}` set `shopname`=:shopname, `scope`=:scope, `token`=:token")
            ->execute(array(':shopname'=>$shopName, ':scope'=>$scope, ':token'=>$token));
    }

    /**
     * @param $shopName
     * @param $scope
     * @return string
     */
    public function getAccessToken($shopName, $scope)
    {
        $st = $this->pdo->prepare("select `token` from `{$this->tableName}` where `shopname`=:shopname and `scope`=:scope");
        $st->execute(array(':shopname'=>$shopName, ':scope'=>$scope));
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        if ($row) return $row['token'];
        return null;
    }

}