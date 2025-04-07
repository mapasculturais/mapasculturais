<?php

if (file_exists($_SERVER['SCRIPT_FILENAME']) && strtolower(substr($_SERVER['SCRIPT_NAME'],-4)) !== '.php') {
    $filename = "/var/www/html" . $_SERVER['SCRIPT_NAME'];
    if(!file_exists($filename)) {
        if ($target = getenv('REDIRECT_404_ASSETS_TO')) {
            header('Location: ' . $target . $_SERVER['SCRIPT_NAME']);
            die;
        } 
        return false;
    }

    $expires = 60 * 5;
    header("Pragma: public");
    header("Cache-Control: maxage=" . $expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

    if(strtolower(substr($filename, -3)) == '.js'){
        $mime = 'text/javascript';
    }elseif(strtolower(substr($filename, -4)) == '.css'){

        $mime = 'text/css';
    }else{
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filename);
        finfo_close($finfo);
    }
    header('Content-type: ' . $mime);
    echo file_get_contents($filename);
    die;  // serve the requested resource as-is.
} else if($_SERVER['SCRIPT_NAME'] === '/index.php') {
    chdir($_SERVER['DOCUMENT_ROOT']);
    include_once 'index.php';
}else{
    return false;
}


