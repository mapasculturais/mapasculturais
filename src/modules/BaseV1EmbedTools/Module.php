<?php

namespace BaseV1EmbedTools;

use MapasCulturais\App;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\Themes\BaseV2;

class Module extends \MapasCulturais\Module
{
    function __construct($config = [])
    {
        /** @var  App $app */
        $app = App::i();

        $config += [];
        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();
        if (strpos($_SERVER['REQUEST_URI'] ?? '/', '/embedtools') === 0) {
            

            $app->view->enqueueScript('app', 'evaluations', 'js/v1-embedtools-evaluations.js');
            $app->view->enqueueScript('app', 'editable', 'js/editable.js');

            $theme_instance = new BaseV1\Theme($app->config['themes.assetManager']);
            $theme_instance->path = $app->view->path;
            $app->view = $theme_instance;
            $app->view->enqueueScript('app', 'embedtools-messages', 'js/embedtools.js', ['mapasculturais']);
            $app->view->enqueueStyle('app', 'embedtools-style', 'css/embedtools.css', ['main']);

            $app->hook(' template(embedtools.reportmanager.reports-footer):before', function () {
                $this->part('create-reports-modal', []);
            });

            $app->hook('template(embedtools.reportmanager.reports-footer):before', function () {
                $this->part('dynamic-reports');
            });

            $app->hook('app.init:after', function () {
                /** @var \ArrayObject $path */
                $path = $this->view->path;

                $basev2_folder = BaseV2\Theme::getThemeFolder() . '/';
                foreach($path as $index => $folder) {
                    if($folder == $basev2_folder) {
                        $path->offsetUnset ($index);
                        break;
                    }
                }
            });
        }

        $app->hook('GET(registration.evaluation):before', function() use($app) {
            $app->view->enqueueScript('app-v2', 'embedtools-evaluations', 'js/embedtools-evaluations.js');
        });
    }

    public function register()
    {
        /** @var  App $app */
        $app = App::i();

        $app->registerController("embedtools", Controller::class);
    }
}
