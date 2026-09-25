<?php

namespace ImageTransformations;

use MapasCulturais\App;

/**
 * Cria os thumbnails e recortes das imagens no momento do upload
 * @package ImageTransformations
 */
class Module extends \MapasCulturais\Module
{
    function _init()
    {
        $app = App::i();
        
        $app->hook('entity(<<*>>).file(avatar).insert:after', function () {
            $this->transform('avatarSmall');
            $this->transform('avatarMedium');
            $this->transform('avatarBig');
        });

        $app->hook('entity(<<*>>).file(header).insert:after', function () {
            $this->transform('header');
        });

        $app->hook('entity(<<subsite>>).file(logo).insert:after', function () {
            $this->transform('logo');
        });

        $app->hook('entity(<<subsite>>).file(background).insert:after', function () {
            $this->transform('background');
        });

        $app->hook('entity(<<subsite>>).file(institute).insert:after', function () {
            $this->transform('institute');
        });

        $app->hook('entity(<<subsite>>).file(favicon).insert:after', function () {
            $this->transform('favicon');
        });

        $app->hook('entity(<<*>>).file(gallery).insert:after', function () {
            $this->transform('galleryThumb');
            $this->transform('galleryFull');
        });
    }

    function register()
    {
    }
}
