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
                    return $app->subsite && $app->user->is('superAdmin');
                }
            ];
        });

        $app->hook('mapas.printJsObject:before', function () use($app) {
            if($app->subsite) {
                $this->jsObject['subsite'] = $app->subsite;
            } else {
                $this->subsite = null;
            }
        });

        $app->hook('view.render(<<*>>):before', function () use($app) {
            $app->view->enqueueScript('components', 'subsite-init', 'js/subsite-init.js', ['components-utils']);
        });

        $app->hook('app.register:after', function () use($app) {

            if ($subsite = $app->subsite) {
                $cache_id = $subsite->getSassCacheId();

                if($app->mscache->contains($cache_id)) {
                    return;
                }
                
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

                $css_map = [
                    'primary',
                    'secondary',
                    'seals',
                    'agents',
                    'events',
                    'opportunities',
                    'projects',
                    'spaces',
                ];

                $variable_part = [];
                $root_part = [];

                foreach ($css_map as $var) {
                    $color = $subsite->{"color_$var"};
                    
                    if ($color) {
                        $variable_part[] = "
                            \$$var-500: $color !default;
                            \$$var-300: lighten(\$$var-500, \$lightness-300) !default;
                            \$$var-700: darken(\$$var-500, \$lightness-700) !default;
                        ";

                        $root_part[] = "
                            --mc-$var-500: #{\$$var-500};
                            --mc-$var-300: #{\$$var-300};
                            --mc-$var-700: #{\$$var-700};
                        ";
                    }
                }


                if (!empty($variable_part) && !empty($root_part)) {
                    $variable_part = implode("\n", $variable_part);
                    $root_part = implode("\n", $root_part);
                    
                    $saas = "
                        @use 'sass:color';

                        // Default lightness deltas
                        \$lightness-300: 25% !default;
                        \$lightness-700: 25% !default;

                        $variable_part

                        :root {
                            $root_part
                        }
                    ";
                    
                    $scss_filename = tempnam(sys_get_temp_dir(), 'subsite-').'.scss';
                    $css_filename = tempnam(sys_get_temp_dir(), 'subsite-').'.css';
                    
                    
                    file_put_contents($scss_filename, $saas);
                    exec("sass $scss_filename $css_filename --no-source-map");
                    
                    $css = file_get_contents($css_filename);
                    
                    $app->hook('template(<<*>>.body):after', function () use ($css) {
                        echo "
                            <style> $css </style>
                        ";
                    });
                }
                if($app->subsite->homeTexts) {
                    foreach($app->subsite->homeTexts as $slug => $text){
                        if(!empty($text)){
                            $app->config["text:$slug"] = $text;
                        }
                    }
                }

                $app->mscache->save($cache_id, 1);
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

            // Logo
            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_title', [
                'label' => i::__("Título da logo do Mapas Culturais"),
                'type' => 'string',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_subtitle', [
                'label' => i::__("Subtítulo da logo do Mapas Culturais"),
                'type' => 'string',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_color1', [
                'label' => i::__("Cor #1"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_color2', [
                'label' => i::__("Cor #2"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_color3', [
                'label' => i::__("Cor #3"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_color4', [
                'label' => i::__("Cor #4"),
                'type' => 'color',
                'default' => null,
            ]);


            // Entidades
            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'color_primary', [
                'label' => i::__("Cor - primária"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'color_secondary', [
                'label' => i::__("Cor - secundária"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'color_seals', [
                'label' => i::__("Cor - selos"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'color_agents', [
                'label' => i::__("Cor - agentes"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'color_events', [
                'label' => i::__("Cor - eventos"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'color_opportunities', [
                'label' => i::__("Cor - oportunidades"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'color_projects', [
                'label' => i::__("Cor - projetos"),
                'type' => 'color',
                'default' => null,
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'color_spaces', [
                'label' => i::__("Cor - espaços"),
                'type' => 'color',
                'default' => null,
            ]);


            // Textos
            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'homeTexts', [
                'label' => i::__("Texto customização"),
                'type' => 'json',
            ]);

            $this->registerFileGroup('subsite', new Definitions\FileGroup('logo',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('background',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'),true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('share',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'),true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('institute',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('favicon',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            
            // Grupos de imagens para customizar
            $this->registerFileGroup('subsite', new Definitions\FileGroup('opportunityBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('eventBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('spaceBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('agentBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('projectBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('signupBanner',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
            $this->registerFileGroup('subsite', new Definitions\FileGroup('header',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
        });
    }
}
