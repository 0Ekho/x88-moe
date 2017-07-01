<?php
define("reveal_include", TRUE);
require_once "./../api/v1/getshort.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>x88.moe</title>
    <meta charset="utf-8">
    <meta name="robots" content="noarchive, noindex">
    <link rel="shortcut icon" href="./favicon.ico" type="image/vnd.microsoft.icon"/>
    <link rel="icon" href="./favicon.ico" type="image/vnd.microsoft.icon"/>
</head>
<body>
    <div>
        This shortlink redirects to: <br>
        <a href="<?php echo($link) ?>"><?php echo($link) ?></a><br>
        You should read the link and make sure you would like to go there before clicking.
    </div>

</body>