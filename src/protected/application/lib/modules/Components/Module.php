<?php
namespace Components;

use MapasCulturais\App;
/**
 * Módulo que implementa a funcionalidade de componentes Vue
 * 
 * @since v5.2
 */
class Module extends \MapasCulturais\Module {
    function _init()
    {
        $app = App::i();

        $app->hook('view.render(<<*>>):before', function () use($app) {
            $vue3 = $app->mode === APPMODE_PRODUCTION ? 
                'https://unpkg.com/vue@3/dist/vue.global.prod.js' : 'https://unpkg.com/vue@3/dist/vue.global.js';

            $app->view->enqueueScript('vendor', 'vue3', $vue3);
            $app->view->enqueueScript('vendor', 'vue-demi', 'https://unpkg.com/vue-demi');
            $app->view->enqueueScript('vendor', 'pinia', 'https://unpkg.com/pinia', ['vue3', 'vue-demi']);
            $app->view->enqueueScript('vendor', 'vue-final-modal', 'https://unpkg.com/vue-final-modal@next', ['vue3']);
            
            $app->view->enqueueScript('app', 'components-api', 'js/components-base/API.js');
            $app->view->enqueueScript('app', 'components-entity', 'js/components-base/Entity.js', ['components-api']);
            
            $app->view->enqueueStyle('vendor', 'vue-final-modal', 'css/components-base/modals.css');
            ;
            if (isset($this->jsObject['componentTemplates'])) {
                $this->jsObject['componentTemplates'] = [];
            }
        });

        $app->hook('mapasculturais.body:after', function () use($app) {
            $app->view->part('components/scripts');
        });

        /**
         * Importa um componente
         * 
         * @param string $component Nome do componente
         * @param array $data Dados para passar na renderizaçao do template do componente
         * @param array $dependences Dependências do componente
         */
        $app->hook('Theme::import', function ($result, string $component, array $data = [], array &$dependences = []) {
            $template = $this->componentRender($component, $data);
            $this->jsObject['componentTemplates'][$component] = $template;

            $this->enqueueComponentScript($component, $dependences);

            if (!in_array($component, $dependences)) {
                $dependences[] = $component;
            }

        });

        /**
         * Enfileira o javascript do componente
         * 
         * @param string $component Nome do componente
         * @param array $dependences Dependências do componente
         */
        $app->hook('Theme::enqueueComponentScript', function ($result, string $component, array $dependences = []) {
            $this->enqueueScript('components', $component, "../components/{$component}/script.js", $dependences);
        });

        /**
         * Renderiza o template do componente informado e retorna o html renderizado
         *  
         * @param string $component Nome do componente
         * @param array $__data Dados Dados para passar na renderizaçao do template do componente 
         * 
         * @return string
         */
        $app->hook('Theme::componentRender', function ($result, string $component, array $__data = []) {
            $app = App::i();
    
            $app->applyHookBoundTo($this, "component({$component}):params", [&$component, &$__data]);
    
            $__template_path = $this->resolveFilename("components/{$component}", 'template.php');
    
            if (!$__template_path) {
                throw new Exceptions\TemplateNotFound("Component {$component} not found");
            }
    
            $app->applyHookBoundTo($this, "component({$component}):before", [&$__template_path]);
    
            ob_start(function ($output) {
                return $output;
            });
    
            if ($app->mode == APPMODE_DEVELOPMENT) {
                echo "<!-- $component -->\n";
            }
    
            include $__template_path;
    
            if ($app->mode == APPMODE_DEVELOPMENT) {
                echo "\n<!-- /$component -->\n";
            }
    
            $__html = ob_get_clean();
    
            $app->applyHookBoundTo($this, "component({$component}):after", [$__template_path, &$__html]);
    
            return $__html;
        });

    }

    function register() {}
}