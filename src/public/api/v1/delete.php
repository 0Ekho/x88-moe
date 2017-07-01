<?php
require_once "./../../../data/config.php";

$bad_request = FALSE;
$status = "Internal Error";
try {
    if (!isset($_GET["d"])) {
        throw new Exception("Bad Request: Missing delete key");
    }
    if (!isset($_GET["i"])) {
        throw new Exception("Bad Request: Missing item key");
    }
} catch (Exception $e) {
    $bad_request = TRUE;
    $status = $e->getMessage();
}

if (!$bad_request) {
    require_once "./../../../Classes/FileHandler.php";
    require_once "./../../../Classes/Utils.php";

    $Util = new Utils();
    $db = new SQLite3($config["database"]);
    $FileHandle = new FileHandler($config, $db);

    $delete_key = $Util->sanitizeArray($_GET["d"]);
    $item_key = $Util->sanitizeArray($_GET["i"]);

    try {
        $urls = $FileHandle->deleteFile($item_key, $delete_key);
        if ($config["log_changes"]) {
            require_once "./../../../Classes/Log.php";
            $Log = new Log($config["changes_log_path"]);
            $Log->log("INFO", "[DELETE_FILE] IP: {$_SERVER['REMOTE_ADDR']}, URL: {$urls['url']}");
        }
        $status = $urls["url"]." Has been deleted";
    } catch (Exception $e) {
        $status = $e->getMessage();
    }
}

echo($status);
?>