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
        'site: name' => $dict->name,
        'site: description' => $dict->texto_sobre,
        'home: welcome' => $dict->texto_boasvindas
      ];
        //            'site: description' => App::i()->config['app.siteDescription'],
        //            'site: in the region' => 'na região',
        //            'site: of the region' => 'da região',
        //            'site: owner' => 'Secretaria',
        //            'site: by the site owner' => 'pela Secretaria',
        //
        //            'home: title' => "Bem-vind@!",
        //            'home: abbreviation' => "MC",
        //            'home: colabore' => "Colabore com o Mapas Culturais",
        //            'home: welcome' => "O Mapas Culturais é uma plataforma livre, gratuita e colaborativa de mapeamento cultural.",
        //            'home: events' => "Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.",
        //            'home: $this->_saasCfgagents' => "Você pode colaborar na gestão da cultura com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural da região. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.",
        //            'home: spaces' => "Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais.",
        //            'home: projects' => "Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.",
        //            'home: home_devs' => 'Existem algumas maneiras de desenvolvedores interagirem com o Mapas Culturais. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o Mapas Culturais é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.',
        //
        //            'search: verified results' => 'Resultados Verificados',
        //            'search: verified' => "Verificados"
    }

    static function getThemeFolder() {
        return __DIR__;
    }

    function _init() {
        $app = App::i();

        //$this->filters = self::$config['filters'];

        $domain = $app->config['app.cache.namespace'];

        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }

        self::$saasCfg = $app->repo('SaaS')->findOneBy(['url' => $domain]);
        //$this->saasCfg->dump();

        $entidades = explode(';', $this->saasCfg->entidades_habilitadas);
        if(!in_array('Agentes', $entidades)){

          $app->_config['app.enabled.agents'] = false;
        }

        if (!in_array('Projetos', $entidades)) {
          $app->_config['app.enabled.projects'] = false;
        }

        if (!in_array('Espacos', $entidades)) {
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

        $this->jsObject['mapsDefaults']['zoomMax']          = $this->saasCfg->zoom_max;
        $this->jsObject['mapsDefaults']['zoomMin']          = $this->saasCfg->zoom_min;
        $this->jsObject['mapsDefaults']['zoomDefault']      = $this->saasCfg->zoom_default;
        $this->jsObject['mapsDefaults']['zoomPrecise']      = $this->saasCfg->zoom_precise;
        $this->jsObject['mapsDefaults']['zoomApproximate']  = $this->saasCfg->zoom_approximate;
        $this->jsObject['mapsDefaults']['includeGoogleLayers'] = $app->config['maps.includeGoogleLayers'];
        $this->jsObject['mapsDefaults']['latitude']         = $this->saasCfg->latitude;
        $this->jsObject['mapsDefaults']['longitude']        = $this->saasCfg->longitude;

        $cache_id = $this->saasCfg->id . ' - _variables.scss';

        if($app->isEnabled('saas') && !$app->cache->contains($cache_id)){
            $variables_scss = "";
            $main_scss = '// Child theme main
@import "variables";
@import "../../../../../src/protected/application/themes/BaseV1/assets/css/sass/main";
';

            if($this->saasCfg->background){
                $backgroundimage = $this->saasCfg->background->url;
                $main_scss .= "
.header-image {
  background-image: url(' . $backgroundimage . ');
}
#home-watermark {
  background-image: url(' . $backgroundimage . ');
}";
            }

            $variables_scss .= "\$brand-agent:   " . (isset($this->saasCfg->cor_agentes)  && !empty($this->saasCfg->cor_agentes)? $saasCfg->cor_agentes: $app->config['themes.brand-agent']) . " !default;\n";
            $variables_scss .= "\$brand-project: " . (isset($this->saasCfg->cor_projetos) && !empty($this->saasCfg->cor_projetos)?$saasCfg->cor_projetos: $app->config['themes.brand-project']) . " !default;\n";
            $variables_scss .= "\$brand-event:   " . (isset($this->saasCfg->cor_eventos)  && !empty($this->saasCfg->cor_eventos)? $saasCfg->cor_eventos: $app->config['themes.brand-event']) . " !default;\n";
            $variables_scss .= "\$brand-space:   " . (isset($this->saasCfg->cor_espacos)  && !empty($this->saasCfg->cor_espacos)? $saasCfg->cor_espacos: $app->config['themes.brand-space']) . " !default;\n";
            $variables_scss .= "\$brand-seal:   " . (isset($this->saasCfg->cor_selos)     && !empty($this->saasCfg->cor_selos)?   $saasCfg->cor_selos: $app->config['themes.brand-seal']) . " !default;\n";
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

    protected function getSaasCfg() {
      return self::$saasCfg;
    }
}
