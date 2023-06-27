<?php

namespace MapasCulturais\Themes\BaseV2;

use MapasCulturais\i;
use MapasCulturais\App;

/**
 * @method void import(string $components) Importa lista de componentes Vue. * 
 */
class Theme extends \MapasCulturais\Theme
{
    function getVersion() {
        return 2;
    }

    static function getThemeFolder()
    {
        return __DIR__;
    }

    function _init()
    {
        $app = App::i();
        $this->bodyClasses[] = 'base-v2';
        $this->enqueueStyle('app-v2', 'main', 'css/theme-BaseV2.css');
        $this->assetManager->publishFolder('fonts');

        // Manifest do five icon
        $app->hook('GET(site.webmanifest)', function() use ($app) {
            /** @var \MapasCulturais\Controller $this */
            $this->json([
                'icons' => [
                    [ 'src' => $app->view->asset('img/favicon-192x192.png', false), 'type' => 'image/png', 'sizes' => '192x192' ],
                    [ 'src' => $app->view->asset('img/favicon-512x512.png', false), 'type' => 'image/png', 'sizes' => '512x512' ],
                 
                ],
            ]);
        });

        $app->hook('template(<<*>>.head):end', function () {
            echo "
        <script>
        document.addEventListener('DOMContentLoaded', (e) => {
            let opacity = 0.01;
            globalThis.opacityInterval = setInterval(() => {
                if(opacity >= 1) {
                    clearInterval(globalThis.opacityInterval);
                }
                document.body.style.opacity = opacity;
                opacity += 0.02;
            },5);
        });
        </script>
";
        });
    }

    function register()
    {
    }
}
