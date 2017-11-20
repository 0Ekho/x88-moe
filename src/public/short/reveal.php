<?php
define("NL_2", TRUE);
require_once "./../api/v1/getshort.php";
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once "../../parts/head.html"; ?>
</head>
<body>
    <div>
        This shortlink redirects to: <br>
        <!-- TODO: improve sanitization from XSS / script injection -->
        <a href="<?php echo($link) ?>"><?php echo($link) ?></a><br>
        Please read the link and confirm you would like to visit before following,<br>
        make sure to check for look-a-like characters/domains.
    </div>

</body>
</html>