<?php
namespace Components;

use MapasCulturais\App;
use MapasCulturais\Exceptions;
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
            $theme = $app->view;

            $vendor_group = $theme instanceof \MapasCulturais\Themes\BaseV2\Theme ? 'vendor-v2' : 'vendor';
            $app_group = $theme instanceof \MapasCulturais\Themes\BaseV2\Theme ? 'app-v2' : 'app';

            $app->view->enqueueScript($app_group, 'components-init', 'js/modules_Components_app.js', []);
            $app->view->enqueueScript($app_group, 'components-api', 'js/components-base/API.js', ['components-init']);
            $app->view->enqueueScript($app_group, 'components-entity', 'js/components-base/Entity.js', ['components-init', 'components-api']);
            $app->view->enqueueScript($app_group, 'components-utils', 'js/components-base/Utils.js', ['components-init']);

            $app->view->enqueueStyle($vendor_group, 'vue-final-modal', 'css/components-base/modals.css');
            ;
            if (isset($this->jsObject['componentTemplates'])) {
                $this->jsObject['componentTemplates'] = [];
            }
        });

        $app->hook('mapasculturais.body:after,template(<<*>>.body):after', function () use($app) {
            $app->view->printScripts('components');;
        });

        $self = $this;
        /** */
        $app->hook('App.get(components)', function (&$result) use($self, $app) {
            $result = $self;
        });

        /**
         * Importa um componente
         *
         * @param string $component Nome do(s) componente(s separados por vírgula)
         * @param array $data Dados para passar na renderizaçao do template do componente
         * @param array $dependences Dependências do componente
         */
        $app->hook('Theme::import', function ($result, string $component, array $data = [], array &$dependences = []) use($app) {
            $component = trim($component);

            if (!$this->importedComponents) {
                $this->importedComponents = [];
            }

            if(preg_match('#[ ,\n]+#', $component) && ($components = preg_split('#[ ,\n]+#', $component))) {
                foreach ($components as $component) {
                    $this->import($component, $data);
                }
                return;
            }

            if (in_array($component, $this->importedComponents)) {
                return;
            }
            $this->importedComponents[] = $component;

            $app->log->debug("importing component {$component}");

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

            extract($__data);

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