<?php
$config = array(
    "domain" => "x88.dev:",
    // does your site have https. there is no reason to disable this unless it is dev server, use LetsEncrypt
    "use_https" => TRUE,
    // how long should the item key be, if set too low you will probably have collusion issues, under 8 is NOT recommended
    "item_key_length" => 8,
    // how long the deletion key should be, if set too low it may become a security issue allowing others to delete your files, under 20 is NOT recommended
    "delete_key_length" => 48,
    // max size of files can be uploaded in bytes (NOTE: you will need to change some PHP settings to accept requests of at least this size)
    "max_file_size" => 268435456, //256 MiB
    // log all sucessfull changes (upload/delete/shorten) to changes_log_path
    "log_changes" => TRUE,
    // where to store logs on changes (upload/delete/shorten) for if someone is posting harmful content they can be blocked
    "changes_log_path" => "/var/www/x88/data/x88_changes.log",
    // where to store the sqlite database file. SHOULD NOT BE PUBLICLY ACCESSIBLE
    "database" => "/var/www/x88/data/x88-moe.db",
    // folder to store the uploaded files on disk, point your $domain/f/ to this folder in your web server
    "upload_path" => "/var/www/x88/public/f/",
    // should anyone be able to upload or only people with API keys?
    "public_api" =>FALSE,
    // max length that the file extention should be, truncated if exceded
    "max_extension_length" => 24,
    // if the shortlinks returned reveal page links by default instead of just a redirect
    "use_reveal_link" => TRUE,
    // how many characters long links are allowed to be, if set too low kind of makes it pointless, but too high and someone could abuse it to use up all your disk space, under 5000 is probably too low.
    "max_link_length" => 8192,
    // file extensions that are banned
    // if public you might want exe, dll, com, bat, and msi (maybe forgetting some other windows things?) files banned
    // it might also be a good idea to run a virus scanner on the uploaded files if public
    // (this is easily gotten around by the uploader just changing the file extension though, probably better to use mime type)
    "banned_file_extensions" => array("msi"),
    // list of domains that should be banned from being used in shortlinks, if public might be usefull to block a site serving viruses or similar issue
    "banned_short_domains" => array("banned.dev")
);
?>
