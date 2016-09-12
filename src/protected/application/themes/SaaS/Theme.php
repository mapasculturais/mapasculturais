<?php
namespace SaaS;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

define('SAAS_PATH', realpath(BASE_PATH . '../SaaS'));

class Theme extends BaseV1\Theme{

    protected $filters = [];

    static protected $config;

    protected $saasPass;

    static protected $saasCfg;

    public function __construct(\MapasCulturais\AssetManager $asset_manager) {
        parent::__construct($asset_manager);
    }

    protected static function _getTexts(){
      $app = App::i();
      $domain = $app->config['app.cache.namespace'];

      if(($pos = strpos($domain, ':')) !== false){
          $domain = substr($domain, 0, $pos);
      }

      $dict = $app->repo('SaaS')->findOneBy(['url' => $domain]);

      return [
        'site: name'        => $dict->name,
        'site: description' => $dict->texto_sobre,
        'home: title'       => $dict->titulo,
        'home: welcome'     => $dict->texto_boasvindas,
        'entities: Spaces'  => $dict->titulo_espacos,
        'entities: Projects'=> $dict->titulo_projetos,
        'entities: Events'  => $dict->titulo_eventos,
        'entities: Agents'  => $dict->titulo_agentes,
        'entities: Seals'   => $dict->titulo_selos
      ];
    }

    static function getThemeFolder() {
        return __DIR__;
    }

    function _init() {
        $app = App::i();
        $saasCfg = '';

        //$this->filters = self::$config['filters'];

        $domain = $app->config['app.cache.namespace'];

        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }

        self::$saasCfg = $app->repo('SaaS')->findOneBy(['url' => $domain]);
        $saasCfg = self::$saasCfg;

        $entidades = explode(';', $saasCfg->entidades_habilitadas);
        if(!in_array('Agentes', $entidades)){

          $app->_config['app.enabled.agents'] = false;
        }

        if (!in_array('Projetos', $entidades)) {
          $app->_config['app.enabled.projects'] = false;
        }

        if (!in_array('Espaços', $entidades)) {
          $app->_config['app.enabled.spaces'] = false;
        }

        if (!in_array('Eventos', $entidades)) {
          $app->_config['app.enabled.events'] = false;
        }

        if (!in_array('Selos', $entidades)) {
          $app->_config['app.enabled.seals'] = false;
        }

        $this->saasPass = SAAS_PATH . '/' . $this->saasCfg->slug;
        $this->addPath($this->saasPass);

        $this->jsObject['mapsDefaults']['zoomMax']          = $saasCfg->zoom_max;
        $this->jsObject['mapsDefaults']['zoomMin']          = $saasCfg->zoom_min;
        $this->jsObject['mapsDefaults']['zoomDefault']      = $saasCfg->zoom_default;
        $this->jsObject['mapsDefaults']['zoomPrecise']      = $saasCfg->zoom_precise;
        $this->jsObject['mapsDefaults']['zoomApproximate']  = $saasCfg->zoom_approximate;
        $this->jsObject['mapsDefaults']['includeGoogleLayers'] = $app->config['maps.includeGoogleLayers'];
        $this->jsObject['mapsDefaults']['latitude']         = $saasCfg->latitude;
        $this->jsObject['mapsDefaults']['longitude']        = $saasCfg->longitude;

        $cache_id = $saasCfg->id . ' - _variables.scss';
        $app->log->debug("Id SaaS: " . $cache_id);
        $app->log->debug("Cache Ok? " . ($app->cache->contains($cache_id)? "Não":"Sim"));
        $app->log->debug("Cache encontrado? " . ($app->cache->fetch($cache_id)? "Sim" : "Não"));
        if($app->isEnabled('saas') && !$app->cache->contains($cache_id)){
            $app->log->debug("Entrou aqui mlk.");
            $variables_scss = "";
            $main_scss = '// Child theme main
@import "variables";
@import "../../../../../src/protected/application/themes/BaseV1/assets/css/sass/main";
';

            if($this->saasCfg->background){
                $backgroundimage = $saasCfg->background->url;
                $main_scss .= "
.header-image {
  background-image: url(' . $backgroundimage . ');
}
#home-watermark {
  background-image: url(' . $backgroundimage . ');
}";
            }

            $variables_scss .= "\$brand-agent:   " . ($saasCfg->cor_agentes?  $saasCfg->cor_agentes:  $app->config['themes.brand-agent'])   . " !default;\n";
            $variables_scss .= "\$brand-project: " . ($saasCfg->cor_projetos? $saasCfg->cor_projetos: $app->config['themes.brand-project']) . " !default;\n";
            $variables_scss .= "\$brand-event:   " . ($saasCfg->cor_eventos?  $saasCfg->cor_eventos:  $app->config['themes.brand-event'])   . " !default;\n";
            $variables_scss .= "\$brand-space:   " . ($saasCfg->cor_espacos?  $saasCfg->cor_espacos:  $app->config['themes.brand-space'])   . " !default;\n";
            $variables_scss .= "\$brand-seal:    " . ($saasCfg->cor_selos?    $saasCfg->cor_selos:    $app->config['themes.brand-seal'])    . " !default;\n";
            $variables_scss .= "\$brand-saas:    " . ($saasCfg->cor_saas?     $saasCfg->cor_agentes:  $app->config['themes.brand-saas'])    . " !default;\n";

            if(!is_dir($this->saasPass . '/assets/css/sass/')) {
              mkdir($this->saasPass . '/assets/css/sass/',0755,true);
            }

            file_put_contents($this->saasPass . '/assets/css/sass/_variables.scss', $variables_scss);
            file_put_contents($this->saasPass . '/assets/css/sass/main.scss', $main_scss);

            putenv('LC_ALL=en_US.UTF-8');
            exec("sass " . $this->saasPass . '/assets/css/sass/main.scss ' . $this->saasPass . '/assets/css/main.css');
        }

        parent::_init();
        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->_publishAssets();
        });

        foreach($this->filters as $controller => $entity_filters){

            $app->hook("API.<<*>>({$controller}).params", function(&$qdata) use($entity_filters){
                //

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

    protected function getSaasCfg() {
      return self::$saasCfg;
    }
}
