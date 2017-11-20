<?php
require_once "GetRandom.php";

class ShortHandler
{
    private static $conf;
    private static $db;
    private static $GetRandom;

    public function __construct($config, $dbo)
    {
        static::$conf = $config;
        static::$db = $dbo;
        static::$GetRandom = new GetRandom;
    }
    public function shortLink($link)
    {
        $this->validateLink($link);
        // FEATURE: add option to use some form of incrementing counter instead to allow for shorter links without having to worry about any collusions
        $delete_key = static::$GetRandom->string(static::$conf["delete_key_length"]);
        $item_key = static::$GetRandom->string(static::$conf["item_key_length"]);
        // same as with FileHandler.php, should probably handle collusions properly
        $this->dbAddLink($item_key, $delete_key, $link);
        return $this->getUrls($item_key, $delete_key);
    }
    public function deleteLink($item_key, $delete_key)
    {
        $this->checkDeleteKey($item_key, $delete_key);
        $this->dbSetDeleted($item_key, $delete_key);
        return $this->getUrls($item_key, $delete_key);
    }
    public function getLink($item_key)
    {
        $stmt = static::$db->prepare("SELECT location FROM shortlinks WHERE item_key=:item_key AND deleted=0");
        $stmt->bindParam(":item_key", $item_key, SQLITE3_TEXT);
        $stmt->bindParam(":delete_key", $delete_key, SQLITE3_TEXT);
        $results = $stmt->execute();
        $result = $results->fetchArray();
        if ($result == FALSE) {
            throw new Exception("Invalid item key");
        }
        return $result["location"];
    }
    // --------------------------------------------------------------------
    private function validateLink($link)
    {
        if (strlen($link) > static::$conf["max_link_length"]) {
            throw new Exception("Link is too long. Max length is ".static::$conf["max_link_length"]);
        }
        $full_domain = parse_url($link);
        // TODO: maybe check for scheme and maybe more
        $full_domain = $full_domain["host"];
        $full_domain = explode('.', $full_domain);
        $domain = end($full_domain).".".prev($full_domain);
        if (in_array($domain, static::$conf["banned_short_domains"])) {
            throw new Exception("Creating shortlinks to ".$domain." is currently not allowed");
        }
    }
    private function dbAddLink($item_key, $delete_key, $link)
    {
        $stmt = static::$db->prepare("INSERT INTO shortlinks (item_key, location, delete_key, deleted) VALUES (:item_key, :link, :delete_key, 0)");
        $stmt->bindParam(":item_key", $item_key, SQLITE3_TEXT);
        $stmt->bindParam(":link", $link, SQLITE3_TEXT);
        $stmt->bindParam(":delete_key", $delete_key, SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result == FALSE) {
            throw new Exception("Error saving link to db");
        }
    }
    private function getUrls($item_key, $delete_key)
    {
        $domain = static::$conf["domain"];
        $reveal = (static::$conf["use_reveal_link"] ? "r" : "s");
        $scheme = (static::$conf["use_https"] ? "https" : "http");
        return array(
            "url" => "$scheme://$domain/$reveal/$item_key",
            "delete_link" => "$scheme://$domain/api/v1/deleteshort.php?i=$item_key&d=$delete_key"
        );
    }
    private function checkDeleteKey($item_key, $delete_key)
    {
        $stmt = static::$db->prepare("SELECT deleted FROM shortlinks WHERE item_key=:item_key AND delete_key=:delete_key");
        $stmt->bindParam(":item_key", $item_key, SQLITE3_TEXT);
        $stmt->bindParam(":delete_key", $delete_key, SQLITE3_TEXT);
        $results = $stmt->execute();
        $result = $results->fetchArray();
        if ($result == FALSE) {
            throw new Exception("Invalid item or deletion key");
        } else if ($result["deleted"] == TRUE) {
            throw new Exception("File was already deleted.");
        } else {
            return FALSE;
        }
    }
    private function dbSetDeleted($item_key, $delete_key)
    {
        $stmt = static::$db->prepare("UPDATE shortlinks SET deleted=1 WHERE item_key=:item_key AND delete_key=:delete_key");
        $stmt->bindParam(":item_key", $item_key, SQLITE3_TEXT);
        $stmt->bindParam(":delete_key", $delete_key, SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result == FALSE) {
            throw new Exception("Error setting link deleted in db");
        }
    }
}
?>