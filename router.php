<?php
// router.php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|svg|ttf|woff|html)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
} else { 
    include_once("./public/index.php");
}