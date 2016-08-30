<?php
namespace SaaS;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

define('SAAS_PATH', realpath(BASE_PATH . '../SaaS'));

class Theme extends BaseV1\Theme{

    protected $filters = [];

    static protected $config;

    protected $saasPass;

    protected $saasCfg;

    public function __construct(\MapasCulturais\AssetManager $asset_manager) {
        parent::__construct($asset_manager);
    }

    protected static function _getTexts(){
        return isset(self::$config['dict']) && is_array(self::$config['dict']) ? self::$config['dict'] : [];
    }

    static function getThemeFolder() {
        return __DIR__;
    }

    function _init() {
        $app = App::i();

        ///self::$config = $app->config['saas'];

        //$this->filters = self::$config['filters'];

        $domain = @$_SERVER['HTTP_HOST'];
        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }

        $this->saasCfg = $app->repo('SaaS')->findOneBy(['url' => "http://$domain"]);
        //$this->saasCfg->dump();die();

        $this->saasPass = SAAS_PATH . '/' . $saasCfg->slug;
        $this->addPath($this->saasPass);

        if($app->isEnabled('saas') && !$app->cache->contains('_variables.css')){
            $variables_scss = "";
            $main_scss = '// Child theme main
@import "variables";
@import "../../../../src/protected/application/themes/BaseV1/assets/css/sass/main";';

            $variables_scss .= "\$brand-agent:   " . (isset($this->saasCfg->cor_agentes)  && !empty($this->saasCfg->cor_agentes)? $saasCfg->cor_agentes: $app->config['themes.brand-agent']) . " !default;\n";
            $variables_scss .= "\$brand-project: " . (isset($this->saasCfg->cor_projetos) && !empty($this->saasCfg->cor_projetos)?$saasCfg->cor_projetos: $app->config['themes.brand-project']) . " !default;\n";
            $variables_scss .= "\$brand-event:   " . (isset($this->saasCfg->cor_eventos)  && !empty($this->saasCfg->cor_eventos)? $saasCfg->cor_eventos: $app->config['themes.brand-event']) . " !default;\n";
            $variables_scss .= "\$brand-space:   " . (isset($this->saasCfg->cor_espacos)  && !empty($this->saasCfg->cor_espacos)? $saasCfg->cor_espacos: $app->config['themes.brand-space']) . " !default;\n";
            $variables_scss .= "\$brand-saas:    " . (isset($this->saasCfg->cor_saas)     && !empty($this->saasCfg->cor_saas)?    $saasCfg->cor_agentes: $app->config['themes.brand-saas']) . " !default;\n";

            if(!is_dir($this->saasPass . '/assets/css/sass/')) {
              mkdir($this->saasPass . '/assets/css/sass/',0755,true);
            }

            file_put_contents($this->saasPass . '/assets/css/sass/_variables.scss', $variables_scss);
            file_put_contents($this->saasPass . '/assets/css/sass/main.scss', $main_scss);

            exec("sass " . $this->saasPass . '/assets/css/sass/main.scss ' . $this->saasPass . '/assets/css/main.css');
        }

        parent::_init();
        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->_publishAssets();
        });

        foreach($this->filters as $controller => $entity_filters){

            $app->hook("API.<<*>>({$controller}).params", function(&$qdata) use($entity_filters){

                foreach($entity_filters as $key => $val){
                    if(!isset($qdata[$key])){
                        $qdata[$key] = $val;
                    } else {
                        $qdata[$key] = "AND($val," . $qdata[$key] . ')';
                    }
                }

            });
        }
    }

    protected function _publishAssets() {
        if($this->saasCfg->getLogo()) {
          $this->jsObject['assets']['logo-instituicao'] = $this->saasCfg->logo->url;
        } else {
          $this->jsObject['assets']['logo-instituicao'] = $this->asset('img/logo-instituicao.png', false);
        }
    }

}
