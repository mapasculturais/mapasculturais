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
    public $templates = [];

    function _init()
    {
        $app = App::i();

        if($app->view->version < 2) {
            return;
        }

        $app->hook('view.render(<<*>>):before', function () use($app) {
            $theme = $app->view;

            $vendor_group = $theme instanceof \MapasCulturais\Themes\BaseV2\Theme ? 'vendor-v2' : 'vendor';
            $app_group = $theme instanceof \MapasCulturais\Themes\BaseV2\Theme ? 'app-v2' : 'app';

            $app->view->enqueueScript('components', 'components-init', 'js/vue-init.js', []);
            $app->view->enqueueScript('components', 'components-api', 'js/components-base/API.js', ['components-init']);
            $app->view->enqueueScript('components', 'components-entityFile', 'js/components-base/EntityFile.js', ['components-init']);
            $app->view->enqueueScript('components', 'components-entityMetalist', 'js/components-base/EntityMetalist.js', ['components-init']);
            $app->view->enqueueScript('components', 'components-mcdate', 'js/components-base/McDate.js');
            $app->view->enqueueScript('components', 'components-entity', 'js/components-base/Entity.js', ['components-init', 'components-api', 'components-entityFile', 'components-entityMetalist', 'components-mcdate']);
            $app->view->enqueueScript('components', 'components-utils', 'js/components-base/Utils.js', ['components-init']);
            $app->view->enqueueScript('components', 'components-global-state', 'js/components-base/global-state.js', ['components-utils']);
            $app->view->enqueueStyle($vendor_group, 'vue-datepicker', '../node_modules/@vuepic/vue-datepicker/dist/main.css');
            $app->view->enqueueStyle($vendor_group, 'floating-vue', '../node_modules/floating-vue/dist/style.css');
            $app->view->enqueueStyle($vendor_group, 'components-carousel', 'css/components-base/carousel.css');
            $app->view->enqueueStyle($vendor_group, 'leaflet', '../node_modules/leaflet/dist/leaflet.css');
            $app->view->enqueueStyle($vendor_group, 'slider', '../node_modules/@vueform/slider/themes/default.css');
            $app->view->enqueueStyle($vendor_group, 'leaflet.markercluster', '../node_modules/leaflet.markercluster/dist/MarkerCluster.css');
            $app->view->enqueueStyle($vendor_group, 'leaflet.markercluster.default', '../node_modules/leaflet.markercluster/dist/MarkerCluster.Default.css');
            $app->view->assetManager->publishFolder('js/vue-init/', 'js/vue-init/');
            $app->view->assetManager->publishFolder('js/media-query/', 'js/media-query/');
            
            
            if (isset($app->components->templates)) {
                $app->components->templates = [];
            }
            
            // Importa componentes globais
            $this->import('
                mc-entity
                mc-icon
                mc-loading
            ');
        });

        $app->hook('mapas.printJsObject:before', function () use($app) {
            $this->jsObject['config']['showIds'] = $app->config['app.showEntityIds'];
            $roles = [];
            $user = $app->user;

            if (!$user->is('guest')) {
                $subsite_id = $app->getCurrentSubsiteId();

                foreach($user->roles as $role) {
                    $role_name = $role->name;
                    $role_definition = $app->getRoleDefinition($role_name);

                    if(!($role_definition->subsiteContext ?? false) || $role->subsiteId == $subsite_id) {
                        $roles[] = $role->name;
                    }
                }

                if ($user->is('admin')) {
                    $roles[] = 'admin';
                }

                if ($user->is('superAdmin')) {
                    $roles[] = 'superAdmin';
                }

                if ($user->is('sassAdmin')) {
                    $roles[] = 'sassAdmin';
                }

                if ($user->is('superSassAdmin')) {
                    $roles[] = 'superSassAdmin';
                }
            }
            
            $this->jsObject['currentUserRoles'] = array_values(array_unique($roles));

            /* Definindo entidades desligadas */
            $entities = ['agents', 'events', 'projects', 'opportunities', 'spaces', 'seals', 'subsites', 'apps'];
            $enabled_entities = [];

            foreach ($entities as $entity) {
                $enabled_entities[$entity] = $app->isEnabled($entity);
            }

            $this->jsObject['enabledEntities'] = $enabled_entities;
        }); 

        $app->hook('mapas.printJsObject:after', function () use($app) {
            $templates = json_encode($app->components->templates);
            echo "\n<script>window.\$TEMPLATES = $templates</script>";
            $app->view->printScripts('components');
            $app->view->printStyles('components');
        });

        $self = $this;
        /** */
        $app->hook('App.get(components)', function (&$result) use($self, $app) {
            $result = $self;
        });

        $app->hook('template(<<*>>.body):begin', function () {
            $this->part('main-app--begin');
        });

        $app->hook('template(<<*>>.body):end', function () {
            $this->insideApp = false;
            $this->part('main-app--end');
        },1000);
        
        if ($app->config['app.mode'] == 'development') {
            $app->hook('template(<<*>>):<<*>>', function () use($app) {
                $hook = $app->hooks->hookStack[count($app->hooks->hookStack) - 1]->name;
                if($this->version >= 2) {
                    $this->import('mc-debug');
                    echo "<mc-debug type='template-hook' name='$hook'></mc-debug>\n";
                }
            });
        }

        /** 
         * Cria um hook para o componente
         * 
         * o hook será no formato "component(component-name):param1" quando só um parâmtro for enviado
         * o hook será no formato "component(component-name).param1:param2" quando 2 parâmtros forem enviados
         * 
         * @param string $param1
         * @param string $param2
         * @param string $sufix
         */
        $app->hook('Theme::applyComponentHook', function ($result, string $sufix, $param1 = [], $param2 = []) use($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */

            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
            preg_match("#.+?/([^/]+)/template.php#", $bt[2]['file'], $match);
            $component_name = $match[1];
            if(is_string($param1)) {
                $hook_name = "component($component_name).$sufix:$param1";
                $params = $param2;
            } else {
                if(preg_match('#^\w#', $sufix)) {
                    $hook_name = "component($component_name):$sufix";
                } else {
                    $hook_name = "component($component_name)$sufix";
                }
                $params = $param1;
            }

            if ($app->mode == APPMODE_DEVELOPMENT) {
                $this->import('mc-debug');
                echo "<mc-debug type='component-hook' name='$hook_name'></mc-debug>";
            }

            $app->applyHookBoundTo($this, $hook_name, $params);
        });

        /**
         * Importa um componente
         *
         * @param string $component Nome do(s) componente(s separados por vírgula)
         * @param array $data Dados para passar na renderizaçao do template do componente
         * @param array $dependences Dependências do componente
         */
        $app->hook('Theme::import', function ($result, string $component, array $data = [], array &$dependences = []) use($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */

            $component = trim($component);

            if (!$this->importedComponents) {
                $this->importedComponents = [];
            }

            if (in_array($component, $this->importedComponents)) {
                return;
            }

            $init_file = $this->resolveFilename("components/{$component}", 'init.php');

            if ($init_file) {
                $app->hook('mapas.printJsObject:before', function () use($init_file, $app, $component) {
                    $started_at = microtime(true);
                    include $init_file;
                    $finished_at = microtime(true);
                    
                    //loga o tempo de execução
                    $sec = $finished_at - $started_at;
                    if($sec  > .1) {
                        $app->log->debug("Component $component init.php executed in " . ($sec) . " seconds");
                    }
                });
            }


            if(preg_match('#[ ,\n]+#', $component) && ($components = preg_split('#[ ,\n]+#', $component))) {
                foreach ($components as $component) {
                    $this->import($component, $data);
                }
                return;
            }
            $imported_components = $this->importedComponents;
            $imported_components[] = $component;
            $this->importedComponents = $imported_components;
            
            if ($app->config['app.log.components']) {
                $app->log->debug("importing component {$component}");
            }

            $template = $this->componentRender($component, $data);
            $app->components->templates[$component] = $template;

            $this->enqueueComponentScript($component, $dependences);
            $this->enqueueComponentStyle($component, $dependences);
            
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
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */

            $texts_filename = $this->resolveFilename("components/{$component}", 'texts.php');
            if($texts_filename && is_file($texts_filename)) {
                $texts = include $texts_filename;
                $this->localizeScript("component:$component", $texts);
            }
            $this->enqueueScript('components', $component, "../components/{$component}/script.js", $dependences);
        });

        /**
         * Enfileira o estilo do componente
         *
         * @param string $component Nome do componente
         * @param array $dependences Dependências do componente
         */
        $app->hook('Theme::enqueueComponentStyle', function ($result, string $component, array $dependences = []) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */

            if($this->resolveFilename("components/{$component}", 'style.css')) {
                $this->enqueueStyle('components', $component, "../components/{$component}/style.css", $dependences);
            }
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
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */

            $app = App::i();

            $app->applyHookBoundTo($this, "component({$component}):params", [&$component, &$__data]);

            $__template_path = $this->resolveFilename("components/{$component}", 'template.php');

            if (!$__template_path) {
                throw new \Exception("Component {$component} not found");
            }

            
            ob_start(function ($output) {
                return $output;
            });

            if ($app->mode == APPMODE_DEVELOPMENT) {
                echo "<!-- $component -->\n";
            }

            $app->applyHookBoundTo($this, "component({$component}):before", [&$__data, &$__template_path]);
            
            extract($__data);

            include $__template_path;
            
            
            $app->applyHookBoundTo($this, "component({$component}):after", [$__data]);
            
            if ($app->mode == APPMODE_DEVELOPMENT) {
                echo "\n<!-- /$component -->";
            }

            $__html = ob_get_clean();

            return $__html;
        });

    }

    function register() {}
}