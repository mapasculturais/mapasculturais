<?php
namespace SaaS;
use MapasCulturais\Themes\BaseV1;
use MapasCulturais\App;

define('SAAS_PATH', realpath(BASE_PATH . '../SaaS'));

class Theme extends BaseV1\Theme{

    protected $filters = [];

    static protected $config;

    protected $saasPass;

    public function __construct(\MapasCulturais\AssetManager $asset_manager) {
        parent::__construct($asset_manager);

        $app = App::i();

        self::$config = $app->config['sass'];

        $this->filters = self::$config['filters'];

        $this->saasPass = SAAS_PATH . '/' . self::$config['slug'];

        $this->addPath($this->saasPass);
    }

    protected static function _getTexts(){
        return isset(self::$config['dict']) && is_array(self::$config['dict']) ? self::$config['dict'] : [];
    }

    static function getThemeFolder() {
        return __DIR__;
    }

    function _init() {
        $app = App::i();

        if($app->isEnabled('saas') && !$app->cache->contains('_variables.css')){
            $variables_scss = "";
            $main_scss = '// Child theme main
                          @import "variables";
                          @import "../../../../../src/protected/application/themes/BaseV1/assets/css/sass/main";
                          ';

            foreach(self::$config['sass-variables'] as $key => $val){
                $variables_scss .= "\${$key}: $val !default;\n";
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
        $this->jsObject['assets']['logo-instituicao'] = $this->asset('img/logo-instituicao.png', false);
    }

}
