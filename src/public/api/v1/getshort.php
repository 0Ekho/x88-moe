<?php
if (defined("reveal_include")) {
    require_once "./../../data/config.php";
} else {
    require_once "./../../../data/config.php";
}

$bad_request = FALSE;
$link = "Internal Error";
try {
    if (!isset($_GET["r"])) {
        throw new Exception("Bad Request: Missing reveal flag");
    }
    if (!isset($_GET["i"])) {
        throw new Exception("Bad Request: Missing item key");
    }
} catch (Exception $e) {
    $bad_request = TRUE;
    $link = $e->getMessage();
}

if (!$bad_request) {
    if (defined("reveal_include")) {
        require_once "./../../Classes/ShortHandler.php";
        require_once "./../../Classes/Utils.php";
    } else {
        require_once "./../../../Classes/ShortHandler.php";
        require_once "./../../../Classes/Utils.php";
    }

    $Util = new Utils();
    $db = new SQLite3($config["database"]);
    $ShortHandle = new ShortHandler($config, $db);

    $item_key = $Util->sanitizeArray($_GET["i"]);

    try {
        $link = $ShortHandle->getLink($item_key);
        if (!defined("reveal_include")) {
            if ($_GET["r"] == 0) {
            header("Location: " . $link, true, 303);
            exit();
            }
        }
    } catch (Exception $e) {
        $link = $e->getMessage();
    }
}
if (!defined("reveal_include")) {
    echo($link);
}
?>