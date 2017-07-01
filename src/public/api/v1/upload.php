<?php
require_once "./../../../data/config.php";

$bad_request = FALSE;
$output = array (
    "url" => "Error",
    "delete_link" => "Error",
    "status" => "Internal Error."
);
// Validate the request at least a little bit before doing much
try {
    if (!isset($_FILES["file"]["error"])) {
        throw new Exception("Bad Request: Request missing file");
    }
    if (is_array($_FILES["file"]["error"])) {
        throw new Exception("Bad Request: Only one file may be uploaded at a time");
    }
    if (!isset($_POST["apikey"]) && !$config["public_api"]) {
        throw new Exception("Bad Request: Missing API key");
    }
    if (isset($_POST["apikey"])) {
        if (is_array($_POST["apikey"])) {
            throw new Exception("Bad Request: Only one API key may be provided");
        }
    }
    switch ($_FILES["file"]["error"]) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new Exception("Bad Request: No file uploaded");
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception("Bad Request: File is too large. Max size is: ".static::$conf["max_file_size"]."bytes");
        default:
            throw new Exception("Bad Request: Unknown file error");
    }
} catch (Exception $e) {
    $bad_request = TRUE;
    $output["status"] = $e->getMessage();
}

if (!$bad_request) {
    require_once "./../../../Classes/FileHandler.php";
    require_once "./../../../Classes/AuthHandler.php";
    require_once "./../../../Classes/Utils.php";

    $Utils = new Utils();
    $db = new SQLite3($config["database"]);
    $AuthHandle = new AuthHandler($config, $db);
    $api_key = $Utils->sanitizeArray($_POST["apikey"]);

    if (!$config["public_api"]) {
        $valid = $AuthHandle->isKeyValid($api_key);
    } else {
        $valid = TRUE;
    }
    if ($valid) {
        $FileHandle = new FileHandler($config, $db);
        try {
            $urls = $FileHandle->saveFile($_FILES["file"]);
            if ($config["log_changes"]) {
                require_once "./../../../Classes/Log.php";
                $Log = new Log($config["changes_log_path"]);
                $used_key = ($config["public_api"] ? "" : ", API KEY: {$api_key}");
                $Log->log("INFO", "[UPLOAD_FILE] IP: {$_SERVER['REMOTE_ADDR']}, URL: {$urls['url']}{$used_key}");
            }
            $output["url"] = $urls["url"];
            $output["delete_link"] = $urls["delete_link"];
            $output["status"] = "OK";
        } catch (Exception $e) {
            $output["status"] = $e->getMessage();
        }
    } else {
        $output["status"] = "Invalid API Key";
    }
    $db->close();
}

// JSON encode was breaking things, might work for you though
echo("{\"url\":\"{$output['url']}\", \"delete_link\":\"{$output['delete_link']}\",\"status\":\"{$output['status']}\"}");
?>