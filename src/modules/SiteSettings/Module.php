<?php

namespace SiteSettings;

use DateTime;
use Exception;
use Throwable;
use MapasCulturais\i;
use MapasCulturais\App;
use SiteSettings\Entities\Settings;

class Module extends \MapasCulturais\Module
{
    /**
     * Arquivo JSON com chaves do reCAPTCHA (google-recaptcha-secret, google-recaptcha-sitekey).
     * Mantido fora do código-fonte do módulo em PRIVATE_FILES_PATH.
     */
    public static function recaptchaAuthFilePath(): string
    {
        return PRIVATE_FILES_PATH . 'SiteSettings/auth.txt';
    }

    /**
     * @return void 
     */
    public function _init(): void
    {
        $app = App::i();

        $self = $this;

        $app->view->enqueueStyle('app-v2', 'SiteSettings-v2', 'css/plugin-SiteSettings.css');

        // Insere a entidade no EntitiesDescription
        $app->hook('mapas.printJsObject:before', function () {
            $this->jsObject['EntitiesDescription']['settings'] = Settings::getPropertiesMetadata();
        });

        // Define a entidade na lista de ENUM
        $app->hook('doctrine.emum(object_type).values', function (&$values) {
            $values['Settings'] = Settings::class;
        });

        // Personalização de ícones
        $app->hook('component(mc-icon).iconset', function (&$iconset) {
            $iconset['one-click-brush'] = "la:brush";
            $iconset['one-click-settings'] = "ic:outline-settings";
            $iconset['one-click-text-outline'] = "mdi:card-text-outline";
            $iconset['one-click-image'] = "majesticons:image";
            $iconset['one-click-colors-sharp'] = "material-symbols:colors-sharp";
            $iconset['one-click-dialog'] = 'wpf:ask-question';
            $iconset['one-click-close-rounded'] = 'material-symbols:close-rounded';

            $iconset['one-click-facebook'] = 'mdi:facebook';
            $iconset['one-click-instagram'] = 'mdi:instagram';
            $iconset['one-click-linkedin'] = 'mdi:linkedin';
            $iconset['one-click-pinterest'] = 'mdi:pinterest';
            $iconset['one-click-spotify'] = 'mdi:spotify';
            $iconset['one-click-tiktok'] = 'mdi:tiktok';
            $iconset['one-click-x'] = 'mdi:twitter';
            $iconset['one-click-vimeo'] = 'mdi:vimeo';
            $iconset['one-click-youtube'] = 'mdi:youtube';
            $iconset['one-click-upload'] = 'et:upload';
            $iconset['one-click-edit'] = 'tabler:edit';
        });

        // Garante o registro de metadados em todas as requisições
        $app->hook('<<*>>(<<*>>.<<*>>):before', function () use ($self) {
            $self->settingsRegisteredMetadata();
        });

        // hook responsável por setar as configurações em seus devidos lugares
        $app->hook('app.register:after', function () use ($self, $app) {
            if (php_sapi_name() != "cli") {
                $app->disableAccessControl();

                $settings = $self->getSettings();

                if ($settings) {
                    $self->setSiteNameSettings($settings, $app);
                    $self->setEmailSettings($settings, $app);
                    $self->setRecaptchaSettings($settings, $app);
                    $self->setGeoSettings($settings, $app);
                    $self->setSocialMedia($settings, $app);
                    $self->setImagesHome($settings, $app);
                    $self->setTextsHome($settings, $app);
                    $self->setLogoDefinitions($settings, $app);
                    $self->setFaviconDefinitions($settings, $app);
                    $self->setShare($settings, $app);
                    $self->setMailImage($settings, $app);
                    $self->setColors($settings, $app);
                    $app->view->jsObject['fromToFilesMetadata'] = $settings->fromToFilesMetadata();
                }

                $app->enableAccessControl();
            }
        });

        // Insere novo menu no painel do usuario
        $app->hook('panel.nav',function(&$nav_items) use($app) {
            $nav_items['siteSettings'] = [
                'label' => i::__('Configurações do site'),
                'condition' => function () use ($app) {
                    return $app->user->is('admin');
                },
                'items' => [
                    ['route' => 'settings/steps', 'icon' => 'one-click-brush', 'label' => i::__('Editor')],
                ]
            ];
        });

        // Título da aba do navegador: a ação "steps" não tem entrada em routes.readableNames
        $app->hook('view.title(settings.steps)', function (&$title) use ($app) {
            $title = i::__('Configurações do site') . ' — ' . i::__('Editor') . ' - ' . $app->siteName;
        });

        // Nome do site no rodapé/cabeçalho dos e-mails: reaplica settings do subsite no momento do render (CLI/jobs).
        $app->hook('app.renderMustacheTemplate:before', function () use ($self, $app) {
            $self->setSiteNameSettings($self->getSettings(), $app);
        });

        // E-mail: resolve a URL aqui (lazy), não só em app.register:after — filas CLI não rodavam esse bloco
        // e, em jobs, o subsite só existe depois do bootstrap; getSettings() usa o contexto atual.
        $app->hook('mustacheTemplate(<<*>>).headerData', function (&$headerData) use ($app, $self) {
            $url = $self->resolveMailHeaderImageUrl($app);
            if (!$url) {
                $url = $app->view->asset('img/mail-image.png', false);
            }
            $headerData->mailHeaderImage = $url;
        });
    }

    /**
     * @return void 
     * @throws Exception 
     */
    public function register(): void
    {
        $app = App::i();

        $app->registerController('settings', Controller::class);

        $this->settingsRegisteredMetadata();
    }

    /**
     * @return void 
     */
    public function settingsRegisteredMetadata(): void
    {
        $app = App::i();
        include __DIR__ . "/registereds/metadata.php";
        foreach ($metadata as $key => $cfg) {
            $this->registerMetadata('SiteSettings\\Entities\\Settings', $key, $cfg);
        }
    }

    /**
     * @param null|Settings $settings
     * @param App $app
     * @return void
     */
    public function setSiteNameSettings(?Settings $settings, App $app): void
    {
        if (!$settings) {
            return;
        }
        $name = is_string($settings->siteName ?? null) ? trim($settings->siteName) : '';
        if ($name !== '') {
            $app->config['app.siteName'] = $name;
        }
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     */
    public function setEmailSettings(?Settings $settings, App $app): void
    {
        $app->config['mailer.templates']['email_teste_settings'] = [
            'title' => i::__("{$app->siteName} - Teste de configuração de email"),
            'template' => 'email_teste_settings.html'
        ];

        $mailer_trasport = "smtp://";

        if ($settings->mailer_user) {
            $mailer_trasport .= $settings->mailer_user;
        }

        if ($settings->mailer_password) {
            $mailer_trasport .= ":{$settings->mailer_password}";
        }

        if ($settings->mailer_host) {
            $mailer_trasport .= "@{$settings->mailer_host}";
        }

        if ($settings->mailer_protocol && $settings->mailer_protocol !== "LOCAL") {
            $mailer_trasport .= $settings->mailer_protocol === 'SSL' ? ':465' : ':587';
        } else {
            $mailer_trasport .= ":1025";
        }

        $app->config['mailer.transport'] = $mailer_trasport;
        $app->config['mailer.from'] = $settings->mailer_email ? $settings->mailer_email : "sysadmin@localhost";
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     */
    public function setRecaptchaSettings(?Settings $settings, App $app): void
    {
        $auth = [];
        if ($settings->recaptcha_secret) {
            $auth['google-recaptcha-secret'] = $settings->recaptcha_secret;
        }

        if ($settings->recaptcha_sitekey) {
            $auth['google-recaptcha-sitekey'] = $settings->recaptcha_sitekey;
        }

        if ($settings->recaptcha_sitekey && $settings->recaptcha_secret) {
            $path = self::recaptchaAuthFilePath();
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
            file_put_contents($path, json_encode($auth));
        }
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     */
    public function setGeoSettings(?Settings $settings, App $app): void
    {
        if ($values = $settings->geoDivisionsFilters) {
            $geoDivisionsFilters = [];
            $fromTo = $this->getFromToGeoFilters();
            foreach ($values as $value) {
                $geoDivisionsFilters[] = $fromTo[$value];
            }

            $app->config['app.geoDivisionsFilters'] = $geoDivisionsFilters;
        }

        if ($values = $settings->geodivisions) {
            $geoDivisionsHierarchy = $this->getFromToGeoDivisionsHierarchy();
            foreach ($values as $value) {
                $name = $geoDivisionsHierarchy[$value];
                $app->config['app.geoDivisionsFilters'][$value] = ['name' => $name, 'showLayer' => true];
            }
        }

        if ($settings->zoom_default) {
            $app->config['maps.zoom.default'] = $settings->zoom_default;
        }

        if ($settings->zoom_max) {
            $app->config['maps.zoom.max'] = $settings->zoom_max;
        }

        if ($settings->zoom_min) {
            $app->config['maps.zoom.min'] = $settings->zoom_min;
        }

        if ($settings->latitude && $settings->longitude) {
            $app->config['maps.center'] = [$settings->latitude, $settings->longitude];
        }
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     */
    public function setSocialMedia(?Settings $settings, App $app): void
    {
        if ($settings->socialmediaData) {
            $socialMedia = (array) $settings->socialmediaData;
            foreach ($socialMedia as $metadata => $link) {
                $app->config['social-media'][$metadata] = [
                    'icon' => $metadata,
                    'link' => $link
                ];
            }
        }
    }

    /**
     * Arquivos sob PUBLIC_PATH viram URL absoluta (o tema repassa URLs http(s) em asset() sem publicar).
     * Fora disso, usa asset relativo ao tema (dados legados).
     */
    public static function resolvePublicAssetUrl(App $app, string $absolutePath, string $themeAssetPath): ?string
    {
        if (!is_readable($absolutePath)) {
            return null;
        }
        $publicRoot = realpath(PUBLIC_PATH);
        if ($publicRoot === false) {
            return null;
        }
        $resolved = realpath($absolutePath);
        if ($resolved === false) {
            return null;
        }
        $publicRoot = rtrim(str_replace('\\', '/', $publicRoot), '/');
        $resolved = str_replace('\\', '/', $resolved);
        if (str_starts_with($resolved, $publicRoot)) {
            $rel = ltrim(substr($resolved, strlen($publicRoot)), '/');
            return rtrim($app->getBaseUrl(), '/') . '/' . $rel;
        }
        try {
            return $app->view->asset($themeAssetPath, false);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function siteSettingsPublicOrThemeAssetUrl(App $app, string $absolutePath, string $themeAssetPath): ?string
    {
        return self::resolvePublicAssetUrl($app, $absolutePath, $themeAssetPath);
    }

    /**
     * @param object{path: string} $imageData
     */
    private function applyHomeImage(App $app, object $imageData, string $configKey): ?string
    {
        $file = $imageData->path ?? '';
        if ($file === '' || !file_exists($file)) {
            return null;
        }
        $url = $this->siteSettingsPublicOrThemeAssetUrl($app, $file, 'img/home/' . basename($file));
        if (!$url) {
            return null;
        }
        $app->config['module.home'][$configKey] = $url;
        return $url;
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     */
    public function setImagesHome(?Settings $settings, App $app)
    {
        $public_banner_url = $settings->bannerImageData
            ? $this->applyHomeImage($app, $settings->bannerImageData, 'home-header')
            : null;

        $public_opportunity_url = $settings->entitiesOpportunityImageData
            ? $this->applyHomeImage($app, $settings->entitiesOpportunityImageData, 'home-opportunities')
            : null;

        $public_event_url = $settings->entitiesEventImageData
            ? $this->applyHomeImage($app, $settings->entitiesEventImageData, 'home-events')
            : null;

        $public_space_url = $settings->entitiesSpaceImageData
            ? $this->applyHomeImage($app, $settings->entitiesSpaceImageData, 'home-spaces')
            : null;

        $public_agent_url = $settings->entitiesAgentImageData
            ? $this->applyHomeImage($app, $settings->entitiesAgentImageData, 'home-agents')
            : null;

        $public_project_url = $settings->entitiesProjectImageData
            ? $this->applyHomeImage($app, $settings->entitiesProjectImageData, 'home-projects')
            : null;

        $public_register_url = $settings->registerImageData
            ? $this->applyHomeImage($app, $settings->registerImageData, 'home-register')
            : null;

        $app->view->jsObject['config']['settingsEditorUploads'] = [
            'home-header' => $public_banner_url,
            'home-opportunities' => $public_opportunity_url,
            'home-events' => $public_event_url,
            'home-spaces' => $public_space_url,
            'home-agents' => $public_agent_url,
            'home-projects' => $public_project_url,
            'home-register' => $public_register_url,
        ];
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     */
    public function setTextsHome(?Settings $settings, App $app)
    {
        if ($bannerTitle = $settings->bannerTitle) {
            $app->config['text:home-header.title'] = $bannerTitle;
        }

        if ($entitiesTitle = $settings->entitiesTitle) {
            $app->config['text:home-entities.title'] = $entitiesTitle;
        }

        if ($entitiesDescription = $settings->entitiesDescription) {
            $app->config['text:home-entities.description'] = $entitiesDescription;
        }

        if ($bannerDescription = $settings->bannerDescription) {
            $app->config['text:home-header.description'] = $bannerDescription;
        }

        if ($entityOpportunityDescription = $settings->entityOpportunityDescription) {
            $app->config['text:home-entities.opportunities'] = $entityOpportunityDescription;
        }

        if ($entityEventDescription = $settings->entityEventDescription) {
            $app->config['text:home-entities.events'] = $entityEventDescription;
        }

        if ($entitySpaceDescription = $settings->entitySpaceDescription) {
            $app->config['text:home-entities.spaces'] = $entitySpaceDescription;
        }

        if ($entityAgentDescription = $settings->entityAgentDescription) {
            $app->config['text:home-entities.agents'] = $entityAgentDescription;
        }

        if ($entityProjectDescription = $settings->entityProjectDescription) {
            $app->config['text:home-entities.projects'] = $entityProjectDescription;
        }

        if ($featureTitle = $settings->featureTitle) {
            $app->config['text:home-feature.title'] = $featureTitle;
        }

        if ($featureDescription = $settings->featureDescription) {
            $app->config['text:home-feature.description'] = $featureDescription;
        }

        if ($registerTitle = $settings->registerTitle) {
            $app->config['text:home-register.title'] = $registerTitle;
        }

        if ($registerDescription = $settings->registerDescription) {
            $app->config['text:home-register.description'] = $registerDescription;
        }

        if ($mapTitle = $settings->mapTitle) {
            $app->config['text:home-map.title'] = $mapTitle;
        }

        if ($mapDescription = $settings->mapDescription) {
            $app->config['text:home-map.description'] = $mapDescription;
        }

        if ($developerTitle = $settings->developerTitle) {
            $app->config['text:home-developers.title'] = $developerTitle;
        }

        if ($developDescription = $settings->developerDescription) {
            $app->config['text:home-developers.description'] = $developDescription;
        }
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     * @throws Exception 
     */
    public function setLogoDefinitions(?Settings $settings, App $app): void
    {
        if ($settings->typeLogoDefinition === 'default') {
            if ($logoDefaultTitle = $settings->logoDefaultTitle) {
                $app->config['logo.title'] = $logoDefaultTitle;
            }

            if ($logoDefaultSubTitle = $settings->logoDefaultSubTitle) {
                $app->config['logo.subtitle'] = $logoDefaultSubTitle;
            }

            $app->config['logo.colors'] = [
                $settings->logoColorPart1 ?: "var(--mc-primary-300)",
                $settings->logoColorPart2 ?: "var(--mc-primary-500)",
                $settings->logoColorPart3 ?: "var(--mc-secondary-300)",
                $settings->logoColorPart4 ?: "var(--mc-secondary-500)",
            ];
        } else {
            $public_logo_url = null;
            if ($imageLogoData = $settings->imageLogoData) {
                $app->config['logo.hideLabel'] = true;
                $path = $imageLogoData->path ?? '';
                if ($path !== '' && file_exists($path)) {
                    $public_logo_url = $this->siteSettingsPublicOrThemeAssetUrl($app, $path, 'img/home/' . basename($path));
                    if ($public_logo_url) {
                        $app->config['logo.image'] = $public_logo_url;
                    }
                }
            }
            $app->view->jsObject['config']['settingsEditorUploads']['logo-image'] = $public_logo_url;
        }
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     * @throws Exception 
     */
    public function setFaviconDefinitions(?Settings $settings, App $app): void
    {
        $public_faviconSVG_url = null;
        if ($faviconSvgData = $settings->faviconSvgData) {
            $path = $faviconSvgData->path ?? '';
            if ($path !== '' && file_exists($path)) {
                $public_faviconSVG_url = $this->siteSettingsPublicOrThemeAssetUrl($app, $path, 'img/home/' . basename($path));
                if ($public_faviconSVG_url) {
                    $app->config['favicon.svg'] = $public_faviconSVG_url;
                }
            }
            $app->view->jsObject['config']['settingsEditorUploads']['favicon-svg'] = $public_faviconSVG_url;
        }

        $public_faviconPNG_url = null;
        if ($faviconPngData = $settings->faviconPngData) {
            $path = $faviconPngData->path ?? '';
            if ($path !== '' && file_exists($path)) {
                $public_faviconPNG_url = $this->siteSettingsPublicOrThemeAssetUrl($app, $path, 'img/home/' . basename($path));
                if ($public_faviconPNG_url) {
                    $app->config['favicon.png'] = $public_faviconPNG_url;
                }
            }
            $app->view->jsObject['config']['settingsEditorUploads']['favicon-png'] = $public_faviconPNG_url;
        }
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     * @throws Exception 
     */
    public function setShare(?Settings $settings, App $app): void
    {
        $public_share_url = null;
        if ($shareData = $settings->shareData) {
            $path = $shareData->path ?? '';
            if ($path !== '' && file_exists($path)) {
                $public_share_url = $this->siteSettingsPublicOrThemeAssetUrl($app, $path, 'img/home/' . basename($path));
                if ($public_share_url) {
                    $app->config['share.image'] = $public_share_url;
                }
                $app->view->jsObject['config']['settingsEditorUploads']['share-image'] = $public_share_url;
            }
        }
    }

    /**
     * URL pública da imagem de cabeçalho dos e-mails para o subsite/contexto atual.
     * Usado no render do Mustache (inclui CLI/jobs) e alinhado ao que está em settings_meta.
     */
    public function resolveMailHeaderImageUrl(App $app): ?string
    {
        $settings = $this->getSettings();
        if (!$settings || !($mailImageData = $settings->mailImageData)) {
            return null;
        }
        $path = $mailImageData->path ?? '';
        if ($path === '' || !is_readable($path)) {
            return null;
        }
        return self::resolvePublicAssetUrl($app, $path, 'img/' . basename($path));
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     * @throws Exception 
     */
    public function setMailImage(?Settings $settings, App $app): void
    {
        if (!$settings) {
            return;
        }

        $public_mail_image = $this->resolveMailHeaderImageUrl($app);

        if ($public_mail_image) {
            $app->config['mailer.headerImage'] = $public_mail_image;
        } else {
            unset($app->config['mailer.headerImage']);
        }

        if (!isset($app->view->jsObject['config']['settingsEditorUploads'])) {
            $app->view->jsObject['config']['settingsEditorUploads'] = [];
        }
        $app->view->jsObject['config']['settingsEditorUploads']['mail-image'] = $public_mail_image;
    }

    /**
     * @param null|Settings $settings 
     * @param App $app 
     * @return void 
     */
    public function setColors(?Settings $settings, App $app)
    {
        if ($settings) {
            $cache_id = 'settingsCustomizerColors';
            $css = null;
            if ($app->mscache->contains($cache_id)) {
                $css = $app->mscache->fetch($cache_id);
            }

            if (!$css) {
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
                    $meta = "{$var}Color";
                    $color = $settings->$meta;

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

                    $scss_filename = tempnam(sys_get_temp_dir(), 'subsite-') . '.scss';
                    $css_filename = tempnam(sys_get_temp_dir(), 'subsite-') . '.css';


                    file_put_contents($scss_filename, $saas);
                    exec("sass $scss_filename $css_filename --no-source-map");

                    $css = file_get_contents($css_filename);


                    $app->mscache->save($cache_id, $css);
                }
            }

            $app->hook('template(<<*>>.body):after', function () use ($css) {
                echo "
                    <style> $css </style>
                ";
            });
        }
    }




    /**
     * Resolve o registro de {@see Settings} do contexto atual (subsite da requisição).
     *
     * - Site principal (`subsite` nulo): usa `subsite_id` nulo; se não existir, cai no registro `id = 1`.
     * - Subsite com `id = 1`: não cria linha nova; reutiliza o registro global `id = 1` se não houver linha com `subsite_id = 1`.
     * - Outros subsites: se não houver linha para aquele `subsite_id`, cria registro com metadados padrão (mesmo conjunto do seed).
     *
     * @return Settings|null
     */
    public function getSettings(): ?Settings
    {
        $app = App::i();
        $repo = $app->repo(Settings::class);

        $subsiteId = $app->subsite ? $app->subsite->id : null;

        if ($settings = $repo->findOneBy(['subsiteId' => $subsiteId])) {
            return $settings;
        }

        if ($subsiteId !== null && (int) $subsiteId !== 1) {
            if ($settings = $this->createDefaultSettingsForSubsite($app, (int) $subsiteId)) {
                return $settings;
            }
        }

        return $repo->findOneBy(['id' => 1]);
    }

    /**
     * Cria `setting` + `setting_meta` padrão para um subsite que ainda não possui configuração.
     *
     * Chamado tipicamente com controle de acesso desligado (ex.: hook `app.register:after` no módulo).
     */
    private function createDefaultSettingsForSubsite(App $app, int $subsiteId): ?Settings
    {
        $repo = $app->repo(Settings::class);
        if ($existing = $repo->findOneBy(['subsiteId' => $subsiteId])) {
            return $existing;
        }

        $defaultsPath = __DIR__ . '/registereds/default-metadata-values.php';
        if (!is_readable($defaultsPath)) {
            $app->log->error('SiteSettings: default-metadata-values.php não encontrado ou ilegível.');
            return null;
        }

        /** @var array<string, string> $defaults */
        $defaults = include $defaultsPath;

        try {
            $settings = new Settings();
            $settings->subsiteId = $subsiteId;
            $settings->status = Settings::STATUS_ACTIVE;

            foreach ($defaults as $key => $value) {
                $settings->setMetadata($key, $value);
            }

            $settings->save(true);
            return $settings;
        } catch (Throwable $e) {
            $app->log->error('SiteSettings: falha ao criar Settings para subsite ' . $subsiteId . ': ' . $e->getMessage());
            return $repo->findOneBy(['subsiteId' => $subsiteId]);
        }
    }

    /**
     * @return array 
     */
    public function getFromToGeoFilters(): array
    {
        return [
            'AC' => 12,
            'AL' => 27,
            'AM' => 13,
            'AP' => 16,
            'BA' => 29,
            'CE' => 23,
            'DF' => 53,
            'ES' => 32,
            'GO' => 52,
            'MA' => 21,
            'MG' => 31,
            'MS' => 50,
            'MT' => 51,
            'PA' => 15,
            'PB' => 25,
            'PE' => 26,
            'PI' => 22,
            'PR' => 41,
            'RJ' => 33,
            'RN' => 24,
            'RS' => 43,
            'RO' => 11,
            'RR' => 14,
            'SC' => 42,
            'SE' => 28,
            'SP' => 35,
            'TO' => 17
        ];
    }

    /**
     * @return array 
     */
    public function getFromToGeoDivisionsHierarchy(): array
    {
        return [
            'pais' => i::__('País'),
            'regiao' => i::__('Região'),
            'estado' => i::__('Estado'),
            'mesorregiao' => i::__('Mesorregião'),
            'microrregiao'     => i::__('Microrregião'),
            'municipio' => i::__('Município'),
            'zona' => i::__('Zona'),
            'subprefeitura' => i::__('Subprefeitura'),
            'distrito' => i::__('Distrito'),
            'setor_censitario' => i::__('Setor Censitario')
        ];
    }

    /**
     * @param string $metadata 
     * @return string 
     */
    public function socialmediaLabels(string $metadata): string
    {
        $from_to = [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'linkedin' => 'Linkedin',
            'pinterest' => 'Pinterest',
            'spotify' => 'Spotify',
            'tiktok' => 'Tiktok',
            'twitter' => 'X Twitter',
            'vimeo' => 'Vimeo',
            'youtube' => 'Youtube'
        ];

        return $from_to[$metadata];
    }
}
