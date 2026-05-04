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

        $app->hook('subsite.applyConfigurations:after', function () use ($app) {
            $subsite = $app->subsite;
            if (!$subsite) {
                return;
            }

            if ($site_name = trim((string) $subsite->site_name)) {
                $app->config['app.siteName'] = $site_name;
            }

            if ($site_description = trim((string) $subsite->site_description)) {
                $app->config['app.siteDescription'] = $site_description;
            }

            if ($share = $subsite->getShareImage()) {
                $app->config['share.image'] = $share->url;
                $app->config['share.image_twitter'] = $share->url;
            }


            if ($mail = $subsite->getMailHeaderImage()) {
                $app->config['mailer.header_image_url'] = $mail->url;
            }

            // Aplica logo image do subsite se configurado para usar imagem
            if ($subsite->logo_use_image === 'image') {
                if ($logoFile = $subsite->getFile('logo')) {
                    $app->config['logo.image'] = $logoFile->url;
                }
            } else {
                $app->config['logo.image'] = '';
            }

            // Aplica ocultação do label do logo
            $app->config['logo.hideLabel'] = ($subsite->logo_hide_label == '1');
        });

        $app->hook('app.register:after', function () use($app) {

            if ($subsite = $app->subsite) {
                $cache_id = $subsite->getSassCacheId();

                // Sempre guardar cores base do tema para o logo-customizer (Vue), mesmo quando o
                // cache Sass já existe — o return abaixo pularia esta atribuição e quebrava a UI.
                Module::$originalColors = $app->config['logo.colors'];

                // Aplica configurações de logo (sempre, independente do cache)
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

                // Aplica homeTexts (sempre, independente do cache)
                if($subsite->homeTexts) {
                    foreach($subsite->homeTexts as $slug => $text){
                        if(!empty($text)){
                            $app->config["text:$slug"] = $text;
                        }
                    }
                }

                // Geração de CSS
                $css = null;
                
                if(!$app->mscache->contains($cache_id)) {
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
                    }

                    $app->mscache->save($cache_id, $css);
                } else {
                    $css = $app->mscache->fetch($cache_id);
                }

                // Sempre injeta o CSS se existir
                if ($css) {
                    $app->hook('template(<<*>>.body):after', function () use ($css) {
                        echo "
                            <style> $css </style>
                        ";
                    });
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

            // Logo
            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_title', [
                'label' => i::__("Título da logo do Mapas Culturais"),
                'type' => 'string',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_subtitle', [
                'label' => i::__("Subtítulo da logo do Mapas Culturais"),
                'type' => 'string',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'site_name', [
                'label' => i::__('Nome do site'),
                'type' => 'string',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'site_description', [
                'label' => i::__('Texto de compartilhamento em redes sociais (ex: Facebook, Twitter, Instagram, etc.)'),
                'type' => 'textarea',
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

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_use_image', [
                'label' => i::__("Tipo de logo"),
                'type' => 'radio',
                'options' => [
                    'css' => i::__('Usar logo padrão (CSS)'),
                    'image' => i::__('Usar imagem personalizada'),
                ],
                'default' => 'css',
            ]);

            $this->view->registerMetadata(\MapasCulturais\Entities\Subsite::class, 'logo_hide_label', [
                'label' => i::__("Ocultar título e subtítulo"),
                'type' => 'boolean',
                'options' => [
                    '1' => i::__('Sim'),
                    '0' => i::__('Não'),
                ],
                'serialize' => function ($value) {
                    return $value  ? '1' : '0';
                },
                'unserialize' => function ($value) {
                    return $value == '1' ? true : false;
                },
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
            $this->registerFileGroup('subsite', new Definitions\FileGroup('mailImage',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true));
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
