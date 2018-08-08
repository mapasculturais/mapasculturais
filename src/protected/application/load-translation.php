<?php
use Sinergi\BrowserDetector\Language;

if ($lcodes = env('APP_LCODE', null)) {
    $lcodes = explode(',', $lcodes);

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

} else {
    $_config = require $config_filename;
    
    $lcode = $_config['app.lcode'];
}

//Load defaut translation textdomain
MapasCulturais\i::load_default_textdomain($lcode);
