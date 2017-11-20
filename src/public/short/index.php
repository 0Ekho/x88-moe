<?php
require_once "./../../data/config.php";
if (isset($_POST["web_upload"])) {
    define("NL_2", TRUE);
    require_once "./../api/v1/makeshort.php";
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once "../../parts/head.html"; ?>
</head>
<body>
<?php if (!isset($_POST["web_upload"])) { ?>
    <form action="#" method="POST">
    <?php if(!$config["public_api"]) { ?>
        <label for="apikey">Access Key</label>
        <input name="apikey" value=""><br>
    <?php } ?>
        <label for="link">URL to shorten</label>
        <input name="link" value=""><br>
        <!-- leave this alone please, changing it simple will cause this page to be shown again and your file to not be processed -->
        <input type="hidden" name="web_upload"/>
        <button>Shorten</button>
    </form>
<?php } else if (strcmp($output['status'], "OK") == 0) { ?>
    <div>
        Your File has been uploaded.<br>
        URL: <a href="<?php echo($output['url']) ?>"><?php echo($output['url']) ?></a><br>
        Deletion URL: <a class="disableLink" href="<?php echo($output['delete_link']) ?>"><?php echo($output['delete_link']) ?></a> save this link, following it later will delete your file.<br>
        (clicking this link has been disabled to help prevent accidental deletion.<br>
    </div>
<?php } else { ?>
    Something may have gone wrong.<br>
    Status: <span><?php echo($output['status']) ?></span><br>
<?php } ?>
</body>
</html>