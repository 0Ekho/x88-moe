<?php
if (defined("NL_2")) {
    require_once "./../../data/config.php";
} else {
    require_once "./../../../data/config.php";
}

$bad_request = FALSE;
$output = array (
    "url" => "Error",
    "delete_link" => "Error",
    "status" => "Internal Error."
);
// Validate the request at least a little bit before doing much
try {
    if (!isset($_POST["link"])) {
        throw new Exception("Bad Request: Missing link");
    }
    if (is_array($_POST["link"])) {
        throw new Exception("Bad Request: Only one link may be shortened at a time");
    }
    if (!isset($_POST["apikey"]) && !$config["public_api"]) {
        throw new Exception("Bad Request: Missing API key");
    }
    if (isset($_POST["apikey"])) {
        if (is_array($_POST["apikey"])) {
            throw new Exception("Bad Request: Only one API key may be provided");
        }
    }
} catch (Exception $e) {
    $bad_request = TRUE;
    $output["status"] = $e->getMessage();
}

if (!$bad_request) {
    if (defined("NL_2")) {
        require_once "./../../Classes/ShortHandler.php";
        require_once "./../../Classes/AuthHandler.php";
        require_once "./../../Classes/Utils.php";
    } else {
        require_once "./../../../Classes/ShortHandler.php";
        require_once "./../../../Classes/AuthHandler.php";
        require_once "./../../../Classes/Utils.php";
    }

    $Utils = new Utils();
    $db = new SQLite3($config["database"]);
    $AuthHandle = new AuthHandler($config, $db);

    if (!$config["public_api"]) {
        $api_key = $Utils->sanitizeArray($_POST["apikey"]);
        $valid = $AuthHandle->isKeyValid($api_key);
    } else {
        $valid = TRUE;
    }
    if ($valid) {
        $ShortHandle = new ShortHandler($config, $db);
        try {
            // TODO: improve sanitization from XSS / script injection, apparently this is still bad.
            $urls = $ShortHandle->shortlink(filter_var($_POST["link"], FILTER_SANITIZE_URL));
            if ($config["log_changes"]) {
                if (defined("NL_2")) {
                    require_once "./../../Classes/Log.php";
                } else {
                    require_once "./../../../Classes/Log.php";
                }
                $Log = new Log($config["changes_log_path"]);
                $used_key = ($config["public_api"] ? "" : ", API KEY: {$api_key}");
                $Log->log("INFO", "[CREATE_SHORT] IP: {$_SERVER['REMOTE_ADDR']}, URL: {$urls['url']}{$used_key}");
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
if (!isset($_POST["web_upload"])) {
    echo("{\"url\":\"{$output['url']}\", \"delete_link\":\"{$output['delete_link']}\",\"status\":\"{$output['status']}\"}");
}
?>