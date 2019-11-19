<?php
use Sinergi\BrowserDetector\Language;

if ($lcodes = env('APP_LCODE', null)) {
    $lcodes = explode(',', $lcodes);

} else {
    $_config = require $config_filename;    
    $lcodes = explode(',', $_config['app.lcode']);
}

$language = new Language();
$browser_lcode = $language->getLanguage();

$lcode = null;

foreach ($lcodes as $lc) {
    if (str_replace('-', '_', $browser_lcode) === $lc) {
        $lcode = $lc;
        break;
    }
}
if (is_null($lcode)) {
    foreach ($lcodes as $lc) {
        if (substr($lc, 0, 2) === substr($browser_lcode, 0, 2)) {
            $lcode = $lc;
            break;
        }
    }
}

if (is_null($lcode)) {
    $lcode = $lcodes[0];
}

//Load defaut translation textdomain
MapasCulturais\i::load_default_textdomain($lcode);
