<?php

namespace ThemeCustomizer;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Definitions;

class Module extends \MapasCulturais\Module
{
    static public $originalColors;

    function __construct(array $config = [])
    {
        $app = App::i();
        if ($app->view instanceof \MapasCulturais\Themes\BaseV2\Theme) {
            parent::__construct($config);
        }
    }

    function _init()
    {
        $app = App::i();

        $app->hook('panel.nav', function (&$group) use ($app) {
            $group['admin']['items'][] = [
                'route' => 'aparencia/index',
                'icon' => 'appearance',
                'label' => i::__('Aparência'),
                'condition' => function () use ($app) {
                    return $app->user->is('superAdmin');
                }
            ];
        });

        $app->hook('mapas.printJsObject:before', function () use($app) {
            $this->jsObject['subsite'] = $app->subsite;
        });

        $app->hook('view.render(<<*>>):before', function () use($app) {
            $app->view->enqueueScript('components', 'subsite-init', 'js/subsite-init.js', ['components-utils']);
        });

        $app->hook('app.register:after', function () use($app) {
            if ($subsite = $app->subsite) {
                
                Module::$originalColors = $app->config['logo.colors'];

                if ($subsite->custom_colors) {
                    if ($color1 = $subsite->logo_color1) {
                        $app->config['logo.colors'][0] = $color1;
                    }

                    if ($color2 = $subsite->logo_color2) {
                        $app->config['logo.colors'][1] = $color2;
                    }

                    if ($color3 = $subsite->logo_color3) {
                        $app->config['logo.colors'][2] = $color3;
                    }

                    if ($color4 = $subsite->logo_color4) {
                        $app->config['logo.colors'][3] = $color4;
                    }
                }

                if ($title = $subsite->logo_title) {
                    $app->config['logo.title'] = $title;
                }

                if ($subtitle = $subsite->logo_subtitle) {
                    $app->config['logo.subtitle'] = $subtitle;
                }
            }
        });

    }

    function register()
    {
        $app = App::i();
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['theme-customizer'])) {
            $app->registerController('theme-customizer', Controller::class);
        }

        $app->hook('app.register', function(){
            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'custom_colors', [
                'label' => i::__('Cores'),
                'type' => 'radio',
                'options' => [
                    '1' => i::__('Utilizar cores customizadas'),
                    '' => i::__('Utilizar cores do tema'),
                ],
                'default' => '',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_color1', [
                'label' => i::__("Cor #1"),
                'type' => 'color',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_color2', [
                'label' => i::__("Cor #2"),
                'type' => 'color',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_color3', [
                'label' => i::__("Cor #3"),
                'type' => 'color',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_color4', [
                'label' => i::__("Cor #4"),
                'type' => 'color',
            ]);
            
            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_title', [
                'label' => i::__("Título da logo do Mapas Culturais"),
                'type' => 'string',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_subtitle', [
                'label' => i::__("Subtítulo da logo do Mapas Culturais"),
                'type' => 'string',
            ]);

            $this->registerFileGroup('subsite', new Definitions\FileGroup('logo',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('background',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'),true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('share',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'),true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('institute',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('favicon',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            
            // Grupos de imagens para customizar
            $this->registerFileGroup('subsite', new Definitions\FileGroup('welcomeBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('opportunityBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('eventBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('spaceBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('agentBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('projectBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('signupBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
        });
    }
}
