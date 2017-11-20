<?php
require_once "./../../data/config.php";
if (isset($_POST["web_upload"])) {
    define("NL_2", TRUE);
    require_once "./../api/v1/upload.php";
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once "../../parts/head.html"; ?>
</head>
<body>
<?php
/* maybe switch to Heredoc? not sure if better
 * http://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
 * Example:
 * $var = <<<EOT
 * some text/HTML
 *    more text
 *    etc
 * EOT;
*/
if (!isset($_POST["web_upload"])) { ?>
    <form action="#" method="POST" enctype=multipart/form-data>
    <?php if(!$config["public_api"]) { ?>
        <label for="apikey">Access Key</label>
        <input name="apikey" value=""><br>
    <?php } ?>
        <label for="file">File to upload (Max size: <?php echo($config['max_file_size_h']) ?>)</label>
        <input type="file" name="file" value=""><br>
        <!-- leave this alone please, changing it simple will cause this page to be shown again and your file to not be processed -->
        <input type="hidden" name="web_upload"/>
        <button>Upload</button>
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