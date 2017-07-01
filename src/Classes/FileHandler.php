<?php
require_once "GetRandom.php";
require_once "Utils.php";

class FileHandler
{
    private static $conf;
    private static $db;
    private static $GetRandom;
    private static $Utils;

    public function __construct($config, $dbo)
    {
        static::$conf = $config;
        static::$db = $dbo;
        static::$GetRandom = new GetRandom;
        static::$Utils = new Utils;
    }
    public function saveFile($file)
    {
        $file_ext = $this->getExtension($file);
        $this->validateFile($file, $file_ext);

        $delete_key = static::$GetRandom->string(static::$conf["delete_key_length"]);
        // FEATURE: add option to use some form of incrementing counter instead to allow for shorter links without having to worry about any collusions
        $item_key = static::$GetRandom->string(static::$conf["item_key_length"]);

        // TODO: handle collusions gracefully, not sure what currently will happen, probably fail to insert (UNIQUE) but not sure if will actually throw an exception
        // Might try to get a key a second time and if it fails again fail the request (or maybe 5 times)
        $this->dbAddFile($item_key, $file_ext, $delete_key);
        $this->saveToDisk($file, $item_key, $file_ext);
        return $this->getURLs($item_key, $file_ext, $delete_key);
    }
    public function deleteFile($item_key, $delete_key)
    {
        $file_ext = $this->checkDeleteKey($item_key, $delete_key);
        $this->dbSetDeleted($item_key, $delete_key);
        $this->deleteFromDisk($item_key, $file_ext);
        return $this->getURLs($item_key, $file_ext, $delete_key);
    }
    // --------------------------------------------------------------------
    private function validateFile($file, $file_ext)
    {
        if ($file["size"] > static::$conf["max_file_size"]) {
            throw new Exception("File is too large. Max size is: ".static::$conf["max_file_size"])."bytes";
        }
        if (in_array($file_ext, static::$conf["banned_file_extensions"])) {
            throw new Exception($file_ext." files are currently blocked");
        }
    }
    private function getExtension($file)
    {
        $name = basename($file["name"]);
        $file_ext = pathinfo($name, PATHINFO_EXTENSION);
        // AFAIK all *real* file extensions are in ASCII so this should be fine even though killing unicode is kindof a stupid thing to do
        // (but if you know of an actuall reason to allow for someting such as ".草" as an extension or the like let me know of a better way to do this)
        $file_ext = static::$Utils->sanitizeArray($file_ext);
        if (strlen($file_ext) > static::$conf["max_extension_length"]) {
            $file_ext = substr($file_ext, 0, static::$conf["max_extension_length"]);
        }
        return $file_ext;
    }
    private function dbAddFile($item_key, $file_ext, $delete_key)
    {
        $stmt = static::$db->prepare("INSERT INTO files (item_key, extension, delete_key, deleted) VALUES (:item_key, :ext, :delete_key, 0)");
        $stmt->bindParam(":item_key", $item_key, SQLITE3_TEXT);
        $stmt->bindParam(":ext", $file_ext, SQLITE3_TEXT);
        $stmt->bindParam(":delete_key", $delete_key, SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result == FALSE) {
            throw new Exception("Error saving file to db");
        }
        return $result;
    }
    private function saveToDisk($file, $item_key, $file_ext)
    {
        $target_file = static::$conf["upload_path"].$item_key.".".$file_ext;
        move_uploaded_file($file["tmp_name"], $target_file);
    }
    private function getURLs($item_key, $file_ext, $delete_key)
    {
        $domain = static::$conf["domain"];
        $scheme = (static::$conf["use_https"] ? "https" : "http");
        return array(
            "url" => "$scheme://$domain/f/$item_key.$file_ext",
            "delete_link" => "$scheme://$domain/api/v1/delete.php?i=$item_key&d=$delete_key"
        );
    }
    private function checkDeleteKey($item_key, $delete_key)
    {
        $stmt = static::$db->prepare("SELECT extension, deleted FROM files WHERE item_key=:item_key AND delete_key=:delete_key");
        $stmt->bindParam(":item_key", $item_key, SQLITE3_TEXT);
        $stmt->bindParam(":delete_key", $delete_key, SQLITE3_TEXT);
        $results = $stmt->execute();
        $result = $results->fetchArray();
        if ($result == FALSE) {
            throw new Exception("Invalid item or deletion key");
        } else if ($result["deleted"] == TRUE) {
            throw new Exception("File was already deleted.");
        } else {
            return $result["extension"];
        }
        return FALSE;
    }
    private function dbSetDeleted($item_key, $delete_key)
    {
        $stmt = static::$db->prepare("UPDATE files SET deleted=1 WHERE item_key=:item_key AND delete_key=:delete_key");
        $stmt->bindParam(":item_key", $item_key, SQLITE3_TEXT);
        $stmt->bindParam(":delete_key", $delete_key, SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result == FALSE) {
            throw new Exception("Error setting file deleted in db");
        }
        return $result;
    }
    private function deleteFromDisk($item_key, $file_ext)
    {
        // TODO error handling
        $target_file = static::$conf["upload_path"].$item_key.".".$file_ext;
        unlink($target_file);
    }
}
?>