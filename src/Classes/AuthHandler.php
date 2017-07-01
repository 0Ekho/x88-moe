<?php
class AuthHandler
{
    private static $db;
    private static $conf;

    public function __construct($config, $dbo)
    {
        static::$conf = $config;
        static::$db = $dbo;
    }
    public function isKeyValid($apikey)
    {
        $stmt = static::$db->prepare("SELECT valid, id FROM apikeys WHERE apikey=:apikey");
        $stmt->bindValue(":apikey", $apikey, SQLITE3_TEXT);
        $results = $stmt->execute();
        $result = $results->fetchArray();
        if ($result == FALSE) {
            return FALSE;
        }
        // sqlite rowids should always be > 0 be default so should not have to worry about returning 0 and getting a false false
        // if you did something weird with your database you might break this, don't set any id negative (or 0) manually,
        return $result["id"];
    }
    public function setKeyValidity($apikey, $is_valid = FALSE)
    {
        // TODO: error handling
        $stmt = static::$db->prepare("UPDATE apikeys SET valid=:valid' WHERE apikey=:apikey");
        $stmt->bindParam(":apikey", $apikey, SQLITE3_TEXT);
        $stmt->bindParam(":valid", $is_valid, SQLITE3_INTEGER);
    }
    public function addKey($name = null)
    {
        // TODO
    }
    public function renameKey($apikey, $name = null)
    {
        // TODO
    }
}
?>