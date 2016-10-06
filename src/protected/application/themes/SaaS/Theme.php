<?php
namespace SaaS;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

define('SAAS_PATH', realpath(BASE_PATH . '../SaaS'));

class Theme extends BaseV1\Theme{

    protected $filters = [];

    static protected $config;

    protected $saasPass;

    /**
     * SaaS Instance
     * 
     * @var \MapasCulturais\Entities\SaaS
     */
    protected $saasInstance;

    public function __construct(\MapasCulturais\AssetManager $asset_manager) {
        parent::__construct($asset_manager);
    }

    protected static function _getTexts(){
        $app = App::i();
        $domain = $app->config['app.cache.namespace'];

        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }

        $saas = $app->repo('SaaS')->findOneBy(['url' => $domain]);
        
        $result = parent::_getTexts();
        
        $saas_texts = [
            'site: name'        => $saas->name,
            'site: description' => $saas->texto_sobre,
            'home: title'       => $saas->titulo,
            'home: welcome'     => $saas->texto_boasvindas,
            'entities: Spaces'  => $saas->titulo_espacos,
            'entities: Projects'=> $saas->titulo_projetos,
            'entities: Events'  => $saas->titulo_eventos,
            'entities: Agents'  => $saas->titulo_agentes,
            'entities: Seals'   => $saas->titulo_selos
        ];
        
        foreach($saas_texts as $key => $val){
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
        
        $domain = $app->config['app.cache.namespace'];

        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }

        $this->saasInstance = $app->repo('SaaS')->findOneBy(['url' => $domain]);
        
        $entidades = explode(';', $this->saasInstance->entidades_habilitadas);
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

        $this->saasPass = SAAS_PATH . '/' . $this->saasInstance->slug;
        $this->addPath($this->saasPass);

        $this->jsObject['mapsDefaults']['zoomMax']          = $this->saasInstance->zoom_max;
        $this->jsObject['mapsDefaults']['zoomMin']          = $this->saasInstance->zoom_min;
        $this->jsObject['mapsDefaults']['zoomDefault']      = $this->saasInstance->zoom_default;
        $this->jsObject['mapsDefaults']['zoomPrecise']      = $this->saasInstance->zoom_precise;
        $this->jsObject['mapsDefaults']['zoomApproximate']  = $this->saasInstance->zoom_approximate;
        $this->jsObject['mapsDefaults']['includeGoogleLayers'] = $app->config['maps.includeGoogleLayers'];
        $this->jsObject['mapsDefaults']['latitude']         = $this->saasInstance->latitude;
        $this->jsObject['mapsDefaults']['longitude']        = $this->saasInstance->longitude;

        $cache_id = $this->saasInstance->getSassCacheId();
        
        if($app->isEnabled('saas') && !$app->cache->contains($cache_id)){
            $app->log->debug("Entrou aqui mlk.");
            $variables_scss = "";
            $main_scss = '// Child theme main
            @import "variables";
            @import "../../../../../src/protected/application/themes/BaseV1/assets/css/sass/main";
            ';

            if($this->saasInstance->background){
                $backgroundimage = $this->saasInstance->background->url;
                $main_scss .= "
                .header-image {
                    background-image: url(' . $backgroundimage . ');
                }
                #home-watermark {
                    background-image: url(' . $backgroundimage . ');
                }";
            }

            $variables_scss .= "\$brand-agent:   " . ($this->saasInstance->cor_agentes?  $this->saasInstance->cor_agentes:  $app->config['themes.brand-agent'])   . " !default;\n";
            $variables_scss .= "\$brand-project: " . ($this->saasInstance->cor_projetos? $this->saasInstance->cor_projetos: $app->config['themes.brand-project']) . " !default;\n";
            $variables_scss .= "\$brand-event:   " . ($this->saasInstance->cor_eventos?  $this->saasInstance->cor_eventos:  $app->config['themes.brand-event'])   . " !default;\n";
            $variables_scss .= "\$brand-space:   " . ($this->saasInstance->cor_espacos?  $this->saasInstance->cor_espacos:  $app->config['themes.brand-space'])   . " !default;\n";
            $variables_scss .= "\$brand-seal:    " . ($this->saasInstance->cor_selos?    $this->saasInstance->cor_selos:    $app->config['themes.brand-seal'])    . " !default;\n";
            $variables_scss .= "\$brand-saas:    " . ($this->saasInstance->cor_saas?     $this->saasInstance->cor_agentes:  $app->config['themes.brand-saas'])    . " !default;\n";

            if(!is_dir($this->saasPass . '/assets/css/sass/')) {
                mkdir($this->saasPass . '/assets/css/sass/',0755,true);
            }

            file_put_contents($this->saasPass . '/assets/css/sass/_variables.scss', $variables_scss);
            file_put_contents($this->saasPass . '/assets/css/sass/main.scss', $main_scss);

            putenv('LC_ALL=en_US.UTF-8');
            exec("sass " . $this->saasPass . '/assets/css/sass/main.scss ' . $this->saasPass . '/assets/css/main.css');
            
            $app->cache->save($cache_id, true);
        }

        parent::_init();
        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->_publishAssets();
        });

        $saas_meta = $app->getRegisteredMetadata("MapasCulturais\Entities\SaaS");
        foreach($saas_meta as $k => $v) {
            $meta_name = $k;

            $pos_meta_filter      = strpos($meta_name,"filtro_");
            $pos_meta_controller  = 0;
            $controller           = "";
            $pos_meta_type        = 0;
            $meta_type            = "";

            if($pos_meta_filter === 0) {
                $meta_name = substr($meta_name,strpos($meta_name,"_")+1);
                $pos_meta_controller = strpos($meta_name,"_");
                if($pos_meta_controller > 0) {
                    $controller = substr($meta_name,0,$pos_meta_controller);
                    $meta_name = substr($meta_name,$pos_meta_controller+1);
                    $pos_meta_type = strpos($meta_name,"_");
                    if($pos_meta_type > 0) {
                        $meta_type = substr($meta_name,0,$pos_meta_type);
                        $meta_name = substr($meta_name,$pos_meta_type+1);
                        if($this->saasInstance->$k) {
                            $meta_name = $meta_type == "term"? "term:".$meta_name: $meta_name;
                            $meta_cont = $this->saasInstance->$k;
                            $meta_cont = is_array($meta_cont)? implode(',',$meta_cont): $meta_cont;
                            $this->filters[$controller] = isset($this->filters[$controller]) ? $this->filters[$controller] : [];
                            $this->filters[$controller][$meta_name] = "IN(" . str_replace(";",",",$meta_cont) . ")";
                        }
                    }
                }
            }
        }

        foreach($this->filters as $controller => $entity_filters){
            $app->log->debug("controller: " . $controller);

            $app->hook("API.<<*>>({$controller}).params", function(&$qdata) use($entity_filters,$app){
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
        if($this->saasInstance->getLogo()) {
            $this->jsObject['assets']['logo-instituicao'] = $this->saasInstance->logo->url;
        } else {
            $this->jsObject['assets']['logo-instituicao'] = $this->asset('img/logo-instituicao.png', false);
        }
    }
}
