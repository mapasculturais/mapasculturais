<?php
namespace Subsite;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

define('SAAS_PATH', realpath(BASE_PATH . '../SaaS'));

class Theme extends BaseV1\Theme{

    static protected $config;

    protected $subsitePath;

    /**
     * Subsite Instance
     *
     * @var \MapasCulturais\Entities\Subsite
     */
    protected $subsiteInstance;

    public function __construct(\MapasCulturais\AssetManager $asset_manager, \MapasCulturais\Entities\Subsite $subsiteInstance) {
        $this->subsiteInstance = $subsiteInstance;

        parent::__construct($asset_manager);
    }

    protected static function _getTexts(){
        $app = App::i();

        $subsite = $app->getCurrentSubsite();

        $result = parent::_getTexts();

        if(is_array($subsite->dict)){
            $subsite_texts = $subsite->dict;
        }

        foreach($subsite_texts as $key => $val){
            if($val){
                $result[$key] = $val;
            }
        }
        return $result;
    }

    static function getThemeFolder() {
        return __DIR__;
    }

    function _init() {
        $app = App::i();

        $that = $this;

        $app->hook('subsite.applyConfigurations:after', function(&$config) use($that){
            $theme_path = $that::getThemeFolder() . '/';
            if (file_exists($theme_path . 'conf-base.php')) {
                $theme_config = require $theme_path . 'conf-base.php';
                $config = array_merge($config, $theme_config);
            }
            if (file_exists($theme_path . 'config.php')) {
                $theme_config = require $theme_path . 'config.php';
                $config = array_merge($config, $theme_config);
            }
        });
        
        $this->subsitePath = SAAS_PATH . '/' . $this->subsiteInstance->url;

        $this->addPath($this->subsitePath);

        $cache_id = $this->subsiteInstance->getSassCacheId();

        if($app->isEnabled('subsite') && !$app->msCache->contains($cache_id)){

            $app->cache->deleteAll();
            if(!is_dir($this->subsitePath . '/assets/css/sass/')) {
                mkdir($this->subsitePath . '/assets/css/sass/',0755,true);
            }
            putenv('LC_ALL=en_US.UTF-8');

            if ($this->subsiteInstance->namespace == 'Subsite'){
                $variables_scss = "";
                $main_scss = '// Child theme main
                @import "variables";
                @import "../../../../../src/protected/application/themes/BaseV1/assets/css/sass/main";
                ';

                if($institute = $this->subsiteInstance->institute){
                    $main_scss .= "
                    .header-image {
                        background-image: url({$institute->url});
                    }";
                }

                if($bg = $this->subsiteInstance->background){
                    $main_scss .= "
                    #home-watermark {
                        background-image: url({$bg->url});
                    }";
                } else {
                    $main_scss .= "
                    #home-watermark {
                        background-image: url('');
                    }";
                }

                $main_scss .= "
                    nav#about-nav{
                        padding: 0.45rem 1.5rem
                    }

                    #organization-logo img {
                        max-height: 60px;
                        max-width: initial;
                        width: initial;
                    }

                ";

                $variables_scss .= "\$brand-agent:   " . ($this->subsiteInstance->cor_agentes?  $this->subsiteInstance->cor_agentes:  $app->config['themes.brand-agent'])   . " !default;\n";
                $variables_scss .= "\$brand-project: " . ($this->subsiteInstance->cor_projetos? $this->subsiteInstance->cor_projetos: $app->config['themes.brand-project']) . " !default;\n";
                $variables_scss .= "\$brand-event:   " . ($this->subsiteInstance->cor_eventos?  $this->subsiteInstance->cor_eventos:  $app->config['themes.brand-event'])   . " !default;\n";
                $variables_scss .= "\$brand-space:   " . ($this->subsiteInstance->cor_espacos?  $this->subsiteInstance->cor_espacos:  $app->config['themes.brand-space'])   . " !default;\n";
                $variables_scss .= "\$brand-seal:    " . ($this->subsiteInstance->cor_selos?    $this->subsiteInstance->cor_selos:    $app->config['themes.brand-seal'])    . " !default;\n";
                $variables_scss .= "\$brand-subsite: " . ($this->subsiteInstance->cor_subsite?  $this->subsiteInstance->cor_subsite:  $app->config['themes.brand-subsite']) . " !default;\n";
                $variables_scss .= "\$brand-primary: " . ($this->subsiteInstance->cor_intro?    $this->subsiteInstance->cor_intro:    $app->config['themes.brand-intro'])   . " !default;\n";
                $variables_scss .= "\$brand-developer:" . ($this->subsiteInstance->cor_dev?     $this->subsiteInstance->cor_dev:      $app->config['themes.brand-developer']) . " !default;\n";

                file_put_contents($this->subsitePath . '/assets/css/sass/_variables.scss', $variables_scss);
                file_put_contents($this->subsitePath . '/assets/css/sass/main.scss', $main_scss);

                exec("sass " . $this->subsitePath . '/assets/css/sass/main.scss ' . $this->subsitePath . '/assets/css/main.css');

                $entidades = explode(';', $this->subsiteInstance->entidades_habilitadas);
                $entities = is_array($entidades)? array_map('strtolower',$entidades): [];

                $entities_second = $entities;
                $entities_third = $entities;

                if(!is_dir($this->subsitePath . '/assets/img/')) {
                    mkdir($this->subsitePath . '/assets/img/',0755,true);
                }

                $entity_file_png = $this->subsitePath . "/assets/img/pin-single-example.png";

                /*  Creates entities pin single and grouped image only for each entity */
                foreach($entities as $entity) {
                    $entity_file_svg = THEMES_PATH . "BaseV1/assets/img/pin-single-example.svg";
                    $entity = iconv('UTF-8', 'ASCII//TRANSLIT', $entity);
                    $entity_first_sing_name = substr($entity, 0, -1);
                    $entity_first_name_color = "cor_" . $entity;
                    if($this->subsiteInstance->$entity_first_name_color) {
                        $entity_icon_img = THEMES_PATH . "BaseV1/assets/img/icon-" . $entity_first_sing_name . ".png";
                        if(file_exists($entity_file_svg)) {
                            $svg = file_get_contents($entity_file_svg);
                            $svg = preg_replace('/class="pin-single-example" fill="\#([0-9a-f]{6})"/','fill="' . $this->subsiteInstance->$entity_first_name_color .'"',$svg);

                            if(file_exists($entity_file_png)) {
                                unlink($entity_file_png);
                            }

                            $im = new \Imagick();
                            $im->setBackgroundColor(new \ImagickPixel('transparent'));
                            $im->readImageBlob($svg);
                            $im->setImageFormat("png24");
                            $im->writeImage($entity_file_png);
                            $im->clear();
                            $im->destroy();

                            if(file_exists($entity_file_png) && file_exists($entity_icon_img)) {
                                $img = \WideImage\WideImage::load($entity_file_png);
                                $watermark = \WideImage\WideImage::load($entity_icon_img);
                                $new = $img->merge($watermark);
                                $new->saveToFile($this->subsitePath . "/assets/img/pin-" . $entity_first_sing_name . ".png");
                            }

                            $entity_file_svg = THEMES_PATH . "/BaseV1/assets/img/pin-" . $entity_first_sing_name. ".svg";
                            if(file_exists($entity_file_svg)) {
                                $im = new \Imagick();
                                $svg = file_get_contents($entity_file_svg);
                                $svg = preg_replace('/class="' . $entity_first_sing_name . '-svg-img" fill="\#([0-9a-f]{6})"/','fill="' . $this->subsiteInstance->$entity_first_name_color .'"',$svg);

                                $im->setBackgroundColor(new \ImagickPixel('transparent'));
                                $im->readImageBlob($svg);

                                /*png settings*/
                                $im->setImageFormat("png24");

                                if(file_exists($this->subsitePath . "/assets/img/agrupador-" . $entity_first_sing_name. ".png")) {
                                    unlink($this->subsitePath . "/assets/img/agrupador-" . $entity_first_sing_name. ".png");
                                }
                                $im->writeImage($this->subsitePath . "/assets/img/agrupador-" . $entity_first_sing_name. ".png");
                                $im->clear();
                                $im->destroy();
                            }
                        }

                        foreach($entities_second as $second_entity) {
                            $second_entity = iconv('UTF-8', 'ASCII//TRANSLIT', $second_entity);
                            $entity_second_sing_name = substr($second_entity, 0, -1);
                            $entity_sec_name_color = "cor_" . $second_entity;
                            if($this->subsiteInstance->$entity_sec_name_color) {
                                $entity_file_svg = THEMES_PATH . "/BaseV1/assets/img/agrupador-combinado-" . $entity_first_sing_name. "-" . $entity_second_sing_name . ".svg";
                                if(file_exists($entity_file_svg)) {
                                    $im = new \Imagick();
                                    $svg = file_get_contents($entity_file_svg);
                                    $svg = preg_replace('/class="' . $entity_first_sing_name . '-svg-img" fill="\#([0-9a-f]{6})"/','fill="' . $this->subsiteInstance->$entity_first_name_color .'"',$svg);
                                    $svg = preg_replace('/class="' . $entity_second_sing_name . '-svg-img" fill="\#([0-9a-f]{6})"/','fill="' . $this->subsiteInstance->$entity_sec_name_color .'"',$svg);

                                    $im->setBackgroundColor(new \ImagickPixel('transparent'));
                                    $im->readImageBlob($svg);

                                    /*png settings*/
                                    $im->setImageFormat("png24");

                                    if(file_exists($this->subsitePath . "/assets/img/agrupador-combinado-" . $entity_first_sing_name. "-" . $entity_second_sing_name . ".png")) {
                                        unlink($this->subsitePath . "/assets/img/agrupador-combinado-" . $entity_first_sing_name. "-" . $entity_second_sing_name . ".png");
                                    }
                                    $im->writeImage($this->subsitePath . "/assets/img/agrupador-combinado-" . $entity_first_sing_name. "-" . $entity_second_sing_name . ".png");
                                    $im->clear();
                                    $im->destroy();
                                }
                            }

                            foreach($entities_third as $third_entity) {
                                $third_entity = iconv('UTF-8', 'ASCII//TRANSLIT', $third_entity);
                                $entity_third_sing_name = substr($third_entity, 0, -1);
                                $entity_thi_name_color = "cor_" . $third_entity;
                                if($this->subsiteInstance->$entity_thi_name_color) {
                                    $entity_file_svg = THEMES_PATH . "/BaseV1/assets/img/agrupador-combinado-"  . $entity_first_sing_name. "-" . $entity_second_sing_name . "-" . $entity_third_sing_name . ".svg";
                                    if(file_exists($entity_file_svg)) {
                                        $im = new \Imagick();
                                        $svg = file_get_contents($entity_file_svg);
                                        $svg = preg_replace('/class="' . $entity_first_sing_name . '-svg-img" fill="\#([0-9a-f]{6})"/','fill="' . $this->subsiteInstance->$entity_first_name_color .'"',$svg);
                                        $svg = preg_replace('/class="' . $entity_second_sing_name . '-svg-img" fill="\#([0-9a-f]{6})"/','fill="' . $this->subsiteInstance->$entity_sec_name_color .'"',$svg);
                                        $svg = preg_replace('/class="' . $entity_third_sing_name . '-svg-img" fill="\#([0-9a-f]{6})"/','fill="' . $this->subsiteInstance->$entity_thi_name_color .'"',$svg);

                                        $im->setBackgroundColor(new \ImagickPixel('transparent'));
                                        $im->readImageBlob($svg);

                                        /*png settings*/
                                        $im->setImageFormat("png24");

                                        if(file_exists($this->subsitePath . "/assets/img/agrupador-combinado.png")) {
                                            unlink($this->subsitePath . "/assets/img/agrupador-combinado.png");
                                        }
                                        $im->writeImage($this->subsitePath . "/assets/img/agrupador-combinado.png");
                                        $im->clear();
                                        $im->destroy();
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $assets_path = $app->config['namespaces'][$this->subsiteInstance->namespace] . "/assets/";
                if(file_exists($assets_path.'css/sass/main.scss'))
                    exec("sass " . $assets_path.'css/sass/main.scss ' . $this->subsitePath . '/assets/css/main.css');
            }
            $app->msCache->save($cache_id, true);
        }

        parent::_init();

        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->_publishAssets();
        });
    }

    protected function _publishAssets() {
        if($this->subsiteInstance->getLogo()) {
            $this->jsObject['assets']['logo-instituicao'] = $this->subsiteInstance->logo->url;
        } else {
            $this->jsObject['assets']['logo-instituicao'] = $this->asset('img/logo-instituicao.png', false);
        }

        if($this->subsiteInstance->getFavicon()) {
            $this->jsObject['assets']['favicon'] = $this->subsiteInstance->favicon->url;
        } else {
            $this->jsObject['assets']['favicon'] = $this->asset('img/favicon.ico', false);
        }
    }
}
