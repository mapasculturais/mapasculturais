<?php

if (file_exists(__DIR__ . '/' . $_SERVER['REQUEST_URI'])) {
    return false;  // serve the requested resource as-is.
} else {
    chdir($_SERVER['DOCUMENT_ROOT']);
    include_once 'index.php';
}

