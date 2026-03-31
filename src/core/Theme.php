<?php
namespace MapasCulturais;

use ArrayObject;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;

/**
 * Classe base para temas do MapasCulturais. Fornece funcionalidades para renderização de templates,
 * gerenciamento de assets e configuração de interface.
 *
 * Ao renderizar, o template pode acessar o objeto view através da variável $this e o controlador
 * que chamou o método render/partial através de $this->controller.
 *
 * @property Controller $controller Controlador que chamou o render/partial
 * @property string $template Template atual sendo renderizado
 * @property \ArrayObject $documentMeta Meta tags do documento
 * @property \ArrayObject $bodyClasses Classes CSS do elemento body
 * @property \ArrayObject $bodyProperties Propriedades do elemento body
 * @property \ArrayObject $jsObject Objeto JavaScript para passar dados ao frontend
 * @property \ArrayObject $path Caminhos de busca para templates e assets
 * 
 * @property-read string $title Título da página
 * @property-read AssetManager $assetManager Gerenciador de assets
 * @property-read int $version Versão do tema
 * 
 * @hook **view.render:before ($template_name)** - executado antes da renderização do template e do layout
 * @hook **view.render({$template_name}):before ($template_name)** - executado antes da renderização do template e do layout
 * @hook **view.partial:before ($template_name)** - executado antes da renderização do template
 * @hook **view.partial({$template_name}):before ($template_name)** - executado antes da renderização do template
 * @hook **view.partial:after ($template_name, $html)** - executado após a renderização do template
 * @hook **view.partial({$template_name}):before ($template_name, $html)** - executado após a renderização do template
 * @hook **view.render:after ($template_name, $html)** - executado após a renderização do template e do layout
 * @hook **view.render({$template_name}):before ($template_name, $html)** - executado após a renderização do template e do layout
 * 
 * @package MapasCulturais
 */
abstract class Theme {
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\MagicCallers,
        Traits\RegisterFunctions;


    /**
     * Controlador que está usando este objeto de view
     * @var \MapasCulturais\Controller
     */
    public $controller;

    /**
     * Template que está sendo renderizado
     * @var string
     */
    protected $template = '';

    /**
     * Indica se o template deve ser renderizado parcialmente (sem layout)
     * @var bool
     */
    protected $_partial = false;

    /**
     * Gerenciador de assets
     * @var AssetManager
     */
    protected $_assetManager = null;

    /**
     * Meta tags do documento
     * @var ArrayObject
     */
    public $documentMeta = [];

    /**
     * Classes CSS para imprimir na tag body
     * @var \ArrayObject
     */
    protected $bodyClasses = null;

    /**
     * Propriedades da tag body
     * @var \ArrayObject
     */
    protected $bodyProperties =  null;

    /**
     * Objeto JavaScript do MapasCulturais
     * @var \ArrayObject
     */
    public $jsObject = null;

    /**
     * Caminhos de busca para templates e assets
     * @var \ArrayObject
     */
    protected $path = null;

    /**
     * Dados passados para o template
     * @var array
     */
    public array $data = [];

    /**
     * Inicialização do tema (método abstrato)
     */
    abstract protected function _init();

    /**
     * Registro do tema (método abstrato)
     */
    abstract function register();

    /**
     * Retorna a versão do tema (método abstrato)
     * @return int
     */
    abstract function getVersion();

    /**
     * Construtor do tema
     * 
     * @param AssetManager $asset_manager Gerenciador de assets
     */
    public function __construct(AssetManager $asset_manager) {
        $this->_assetManager = $asset_manager;

        $app = App::i();
        
        $this->documentMeta = new \ArrayObject;
        $this->bodyClasses = new \ArrayObject;
        $this->bodyProperties = new \ArrayObject;

        $this->jsObject = new \ArrayObject;
        $this->jsObject['baseURL'] = $app->baseUrl;
        $this->jsObject['assetURL'] = $app->assetUrl;
        $this->jsObject['maxUploadSize'] = $app->getMaxUploadSize($useSuffix=false);
        $this->jsObject['maxUploadSizeFormatted'] = $app->getMaxUploadSize();
        $this->jsObject['EntitiesDescription'] = [];
        $this->jsObject['config'] = [
            'locale' => str_replace('_', '-', $app->config['app.lcode']),
            'timezone' => date_default_timezone_get(),
            'currency' => $app->config['app.currency']
        ];

        
        $app->hook('app.init:after', function(){
            $this->view->jsObject['userId'] = $this->user->is('guest') ? null : $this->user->id;
            $this->view->jsObject['user'] = $this->user;
            $this->view->jsObject['routes'] = $this->config['routes'];
        });

        $app->hook('app.register', function() use($app){
            $def = new Definitions\Metadata('sentNotification', ['label' => 'Notificação enviada', 'type' => 'boolean']);

            $app->registerMetadata($def, 'MapasCulturais\Entities\Agent');
            $app->registerMetadata($def, 'MapasCulturais\Entities\Space');
        });
        
        $app->hook('mapas.printJsObject:before', function () use($app) {
            if ($app->view->version >= 2) {
                $this->jsObject['request'] = [
                    'controller' => $app->view->controller->id,
                    'action' => $app->view->controller->action,
                    'urlData' => $app->view->controller->urlData,
                ];

                $this->jsObject['request']['id'] = $app->view->controller->data['id'] ?? null;
            }
          
            $this->jsObject['EntitiesDescription'] = [
                "user"          => Entities\User::getPropertiesMetadata(),
                "agent"         => Entities\Agent::getPropertiesMetadata(),
                "event"         => Entities\Event::getPropertiesMetadata(),
                "eventoccurrence" => Entities\EventOccurrence::getPropertiesMetadata(),
                "space"         => Entities\Space::getPropertiesMetadata(),
                "project"       => Entities\Project::getPropertiesMetadata(),
                "opportunity"   => Entities\Opportunity::getPropertiesMetadata(),
                "registration"   => Entities\Registration::getPropertiesMetadata(),
                "subsite"       => Entities\Subsite::getPropertiesMetadata(),
                "seal"          => Entities\Seal::getPropertiesMetadata(),
                'evaluationmethodconfiguration' => Entities\EvaluationMethodConfiguration::getPropertiesMetadata(),
                "chatthread"   => Entities\ChatThread::getPropertiesMetadata(),
                "chatmessage"   => Entities\ChatMessage::getPropertiesMetadata(),
                "registrationevaluation"   => Entities\RegistrationEvaluation::getPropertiesMetadata(),
            ];

            $taxonomies = [];
            foreach($app->getRegisteredTaxonomies() as $slug => $definition) {
                $taxonomy = $definition->jsonSerialize();
                $taxonomy['terms'] = array_values($taxonomy['restrictedTerms']);

                unset($taxonomy['id'], $taxonomy['slug'], $taxonomy['restrictedTerms']);
                
                $taxonomies[$slug] = $taxonomy;
            }
            $this->jsObject['Taxonomies'] = $taxonomies;
        });


        $this->path = new \ArrayObject();

        $self = $this;
        $class = get_called_class();

        $app->hook('app.modules.init:after', function() use($class, $self){
            $reflaction = new \ReflectionClass($class);
        
            while($reflaction->getName() != __CLASS__){
                $dir = dirname($reflaction->getFileName());
                if($dir != __DIR__) {
                    $self->addPath($dir);
                }
                $reflaction = $reflaction->getParentClass();
            }
        }, 100);
        
        $app->hook('app.init:after', function () use ($app) {

            if ((!$app->user->is('guest')) && $app->config['app.redirect_profile_validate']) {

                if ($app->view->version < 2) {
                    $app->hook('view.partial(nav-main-user).params', function ($params, &$name) {
                        $name = 'header-profile-link';
                    });
                }

                // redireciona o usuário para a edição do perfil se este não estiver publicado
                $app->hook('GET(panel.<<*>>):before, GET(<<*>>.<<edit|create|single>>):before, auth.successful', function () use ($app) {
                    $url = $app->user->profile->editUrl . '?notification_handler=true';

                    $redirect = false;
                    if($app->user->profile->status < 1 || $app->user->profile->validationErrors) {
                        $redirect = true;
                    }

                    // Hook do template para exibir alerta de campos obrigatórios
                    $app->hook('template(agent.edit.entity-info-validation):begin', function () use ($app, $redirect) {
                        if ($redirect && isset($_GET['notification_handler']) && $_GET['notification_handler'] == 'true') {
                            $this->part("required-fields-profile-message");
                        }
                    });

                    if ($entity = $this->requestedEntity) {
                        /** @var \MapasCulturais\Entity $entity */
                        if (!$entity->equals($app->user->profile) && $redirect) {
                            $app->redirect($url);
                        }
                    } 
                });

            }
        });


        $reflaction = new \ReflectionClass(get_class($this));
        
        while($reflaction->getName() != __CLASS__){
            $dir = dirname($reflaction->getFileName());
            if($dir != __DIR__) {
                i::addReplacements($dir . '/translations/replacements');
            }
            $reflaction = $reflaction->getParentClass();
        }
    }

    /**
     * Inicializa o tema
     */
    function init(){
        $app = App::i();
        $app->applyHookBoundTo($this, 'theme.init:before');
        $this->_init();
        $app->applyHookBoundTo($this, 'theme.init:after');
    }

    
    /**
     * Nome do último arquivo que teve o log de texto impresso
     * @var string
     */
    private $__previousLoggedFilename = '';

    /**
     * Retorna um texto configurável baseado no contexto
     * 
     * Quando chamada passando $name = 'title', a função procurará o texto
     * nas chaves de configuração respeitando a ordem de prioridade baseada no contexto.
     * 
     * @param string $name Nome do texto
     * @param string $default_localized_text Texto padrão localizado
     * @return string Texto configurado ou padrão
     */
    function text(string $name, string $default_localized_text) {
        $app = App::i();

        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1);
        $caller_filename = $bt[0]['file'];

        if ($conf = $app->_config['app.log.texts']) {
            $filename = str_replace(APPLICATION_PATH, '', $caller_filename);
            if($filename != $this->__previousLoggedFilename) {
                $this->__previousLoggedFilename = $filename;
                $app->log->debug("text > \033[37m{$filename}\033[0m");
            }
        }

        $keys = [];

        $controller_id = $this->controller->id;
        $action = $this->controller->action;

        // TEMPLATE PART
        if(preg_match("#layouts/parts/(.*?)\.php$#", $caller_filename, $matches)){
            $match = $matches[1];
            $keys = [
                "text:{$controller_id}.{$action}.part($match).title",
                "text:*.{$action}.part($match).title",
                "text:{$controller_id}.*.part($match).title",
                "text:part($match).title",
            ];

        // LAYOUT
        }elseif(preg_match("#layouts/([^/]*?)\.php$#", $caller_filename, $matches)) {
            $match = $matches[1];
            $keys = [
                "text:{$controller_id}.{$action}.layout({$match}).{$name}",
                "text:*.{$action}.layout({$match}).{$name}",
                "text:{$controller_id}.*.layout({$match}).{$name}",
                "text:layout({$match}).{$name}",
            ];

        // VIEWS
        }elseif(preg_match("#views/{$controller_id}/([^/]*?)\.php$#", $caller_filename, $matches)) {
            $match = $matches[1];
            $keys = [
                "text:{$controller_id}.{$action}.view({$match}).{$name}",
                "text:*.{$action}.view({$match}).{$name}",
                "text:{$controller_id}.*.view({$match}).{$name}",
                "text:view({$match}).{$name}",
            ];

        // COMPONENTS
        } else if (preg_match("#components/([^/]+)/[^/]+.php#", $caller_filename, $matches)) {
            $match = $matches[1];
            $keys = [
                "text:{$controller_id}.{$action}.{$match}.{$name}",
                "text:*.{$action}.{$match}.{$name}",
                "text:{$controller_id}.*.{$match}.{$name}",
                "text:{$match}.{$name}",
            ];
        }

        foreach($keys as $key) {
            if ($conf = $app->_config['app.log.texts']) {
                if(is_bool($conf) || preg_match('#' . str_replace('*', '.*', $conf) . '#i', $key)){
                    $app->log->debug("text >> \033[33m{$key}\033[0m");
                }
            }

            if($text = $app->_config[$key] ?? false) {
                return $text;
            }
        }

        return $default_localized_text;
    }

    /**
     * Define a propriedade partial
     *
     * Use passando true quando quiser renderizar o template sem o layout.
     *
     * @param bool $val
     */
    public function setPartial($val){
        $this->_partial = $val;
    }

    /**
     * Define o layout a ser usado
     * @param string $name Nome do layout
     */
    public function setLayout($name){
        $this->controller->layout = $name;
    }

    /**
     * Define o controlador
     *
     * @param \MapasCulturais\Controller $controller Controlador
     */
    public function setController(\MapasCulturais\Controller $controller){
        $this->controller = $controller;
    }

    /**
     * Renderiza o template
     *
     * Dentro do template o objeto view é a variável $this e o controlador
     * que chamou o template é $this->controller.
     *
     * Se a propriedade "partial" estiver definida como true, o template será
     * renderizado sem o layout.
     *
     * @param string $template Nome do template
     * @param array $data Dados para o template
     * @return string Template renderizado
     */
    public function render($template, array $data = []){
        $app = App::i();

        $this->template = $template;

        $this->data = $data;

        if ($this->_partial) {
            $output = $this->partialRender ($template, $data);
        } else {
            $output = $this->fullRender ($template, $data);
        }

        $app->response->getBody()->write($output);
    }

    /**
     * Renderiza o template com o layout
     *
     * Para alterar o layout que será usado, defina a propriedade "layout" deste objeto
     * dentro do arquivo de template. Dentro do arquivo de template, o objeto view é a
     * variável $this, então para definir o layout você pode fazer: $this->layout = 'meu-layout';
     *
     * Este método extrai o array "data" para tornar as variáveis acessíveis dentro do template.
     *
     * @param string $__template Template a ser renderizado
     * @param array|null $data Dados para o template
     *
     * @hook **view.render:before ($template_name)** - executado antes da renderização do template e do layout
     * @hook **view.render({$template_name}):before ($template_name)** - executado antes da renderização do template e do layout
     * @hook **view.render:after ($template_name, $html)** - executado após a renderização do template e do layout
     * @hook **view.render({$template_name}):before ($template_name, $html)** - executado após a renderização do template e do layout
     *
     * @return string Template renderizado
     */
    public function fullRender($__template, $data = null){
        $app = App::i();

        $app->applyHookBoundTo($this, 'view.render(' . $__template . ').params', [&$data, &$__template]);

        $__template_filename = strtolower(substr($__template, -4)) === '.php' ? $__template : $__template . '.php';
        $render_data = [];

        foreach($data as $k => $val){
            $render_data[$k] = $val;
            $$k = $val;
        }

        $controller = $this->controller;
        
        $this->bodyClasses[] = "controller-{$controller->id}";
        $this->bodyClasses[] = "action-{$controller->action}";
        $this->bodyClasses[] = "layout-{$controller->layout}";
        
	    if(isset($entity)){
            $this->bodyClasses[] = 'entity';
        }

        // render the template
        $__templatePath = $this->resolveFilename('views', $__template_filename);

        if(!$__templatePath){
            throw new \Exception("Template $__template_filename not found");
        }

        $__template_name = preg_replace('#(.*\/)([^\/]+\/[^\/\.]+)(\.php)?$#', '$2', $__templatePath);

        $app->applyHookBoundTo($this, 'view.render(' . $__template_name . '):before', ['template' => $__template_name]);

        $TEMPLATE_CONTENT = $this->partialRender($__template_name, $data);

        $__layout_filename = strtolower(substr($controller->layout, -4)) === '.php' ? $controller->layout : $controller->layout . '.php';

        // render the layout with template
        $__layoutPath = $this->resolveFilename('layouts', $__layout_filename);

        if(strtolower(substr($__layoutPath, -4)) !== '.php')
                $__layoutPath .= '.php';

        ob_start(function($output){
            return $output;
        });

        $app->applyHookBoundTo($this, 'view.renderLayout(' . $controller->layout . '):before', ['template' => $__template_name]);
        
        include $__layoutPath;

        $app->applyHookBoundTo($this, 'view.renderLayout(' . $controller->layout . '):after', ['template' => $__template_name]);

        $__html = ob_get_clean();

        $app->applyHookBoundTo($this, 'view.render(' . $__template_name . '):after', ['template' => $__template_name, 'html' => &$__html]);

        return $__html;
    }

    /**
     * Renderiza o template sem o layout
     *
     * Este método é chamado quando a propriedade "partial" foi definida como true
     * antes do método render ser chamado.
     *
     * Este método extrai o array de dados para tornar as variáveis acessíveis dentro do template.
     *
     * @param string $__template Template a ser renderizado
     * @param array $__data Dados a serem passados para o template
     * @param bool $_is_part Indica se é uma partial (parte de layout)
     *
     * @hook **view.partial:before ($template_name)** - executado antes da renderização do template
     * @hook **view.partial({$template_name}):before ($template_name)** - executado antes da renderização do template
     * @hook **view.partial:after ($template_name, $html)** - executado após a renderização do template
     * @hook **view.partial({$template_name}):before ($template_name, $html)** - executado após a renderização do template
     *
     * @return string Template renderizado
     */
    public function partialRender($__template, $__data = [], $_is_part = false){
        $app = App::i();
        
        if($__data instanceof \Slim\Helper\Set){
            $_data = $__data;
            $__data = [];
            
            foreach($_data->keys() as $k){
                $__data[$k] = $_data->get($k);
            }

        }
        
        $app->applyHookBoundTo($this, 'view.partial(' . $__template . ').params', [&$__data, &$__template]);

        if(strtolower(substr($__template, -4)) === '.php'){
            $__template_filename = $__template;
            $__template = substr($__template, 0, -4);
        } else {
            $__template_filename = $__template . '.php';
        }
        
        if(is_array($__data)){
            extract($__data);
        }

        // render the template
        if($_is_part){
            $__templatePath = $this->resolveFilename('layouts', 'parts/' . $__template_filename);
        }else{
            $__templatePath = $this->resolveFilename('views', $__template_filename);

        }
        
        if(!$__templatePath){
            throw new \Exception("Template $__template_filename not found");

        }

        $__template_name = substr(preg_replace('#^'.$this->templatesDirectory.'/?#', '', $__templatePath),0,-4);


        $app->applyHookBoundTo($this, 'view.partial(' . $__template . '):before', ['template' => $__template]);

        ob_start(function($output){
            return $output;
        });
        
        if ($app->mode == APPMODE_DEVELOPMENT) {
            $template_debug = str_replace(THEMES_PATH, '', $__template_name);
            $template_debug = str_replace(MODULES_PATH, 'modules/', $template_debug);
            $template_debug = str_replace(PLUGINS_PATH, 'plugins/', $template_debug);
            echo '<!-- ' . $template_debug . ".php # BEGIN -->";
        }

        include $__templatePath;
        
        if ($app->mode == APPMODE_DEVELOPMENT) {
            echo '<!-- ' . $template_debug . ".php # END -->";
        }

        $__html = ob_get_clean();

        $app->applyHookBoundTo($this, 'view.partial(' . $__template . '):after', ['template' => $__template, 'html' => &$__html]);

        return $__html;
    }

    /**
     * Render a template without the layout.
     *
     * Use this method inside templates to include some part of html. This method call the method partialRender to render the
     * template part.
     *
     * If no folder is specified in $template param, the folder "parts" inside the layout folder will be used, so if you call
     * $this->part('foo'), the themes/active/layout/parts/foo.php will be rendered. Otherwise, if you call $this->part('foo/boo'),
     * the themes/active/views/foo/boo.php will be included.
     *
     * @param string $template
     * @param array $data Data to be passed to template part.
     */
    public function part($template, $data = []){
        echo $this->partialRender($template, $data, true);
    }

    function getTitle($entity = null){
        $app = App::i();
        $title = '';
        if($entity){
            $title = $entity->name . ' - ' . $app->siteName;
        }elseif($this->controller->id == 'site' && $this->controller->action === 'index'){
            $title = $app->siteName;
        }elseif($this->controller->id == 'panel' && $this->controller->action === 'index'){
            $title = $app->getReadableName('panel');
        }else{
            $title =$app->getReadableName($this->controller->action);
        }

        $app->applyHookBoundTo($this, 'mapasculturais.getTitle', [&$title]);
        $app->applyHookBoundTo($this, "view.title({$this->controller->id}.{$this->controller->action})" , [&$title]);

        return $title;
    }

    function addPath($path){
        if(substr($path,-1) !== '/') $path .= '/';

        $this->path[] = (string) $path;
    }

    /**
     *
     * @return \MapasCulturais\AssetManager
     */
    function getAssetManager(){
        return $this->_assetManager;
    }

    function enqueueScript($group, $script_name, $script_filename, array $dependences = []){
        $app = App::i();
        if($app->config['app.log.assets']){
            $dep = implode(', ', $dependences);
            $app->log->debug("enqueueScript ({$group}) {$script_name} : {$script_filename} ({$dep})");
        }
        $this->_assetManager->enqueueScript($group, $script_name, $script_filename, $dependences);
    }

    function enqueueStyle($group, $style_name, $style_filename, array $dependences = [], $media = 'all'){
        $app = App::i();
        if($app->config['app.log.assets']){
            $dep = implode(', ', $dependences);
            $app->log->debug("enqueueScript ({$group}) {$style_name} : {$style_filename} ({$dep})");
        }
        $this->_assetManager->enqueueStyle($group, $style_name, $style_filename, $dependences, $media);
    }
    
    /**
     * Add localization strings to a javascript object
     *
     * It simply adds the strings to the jsObject property that can be accessed throug the MapasCulturais javascript object.
     * 
     * Example: 
     * 
     * $this->localizeScript('myScript', ['noresults' => \MapasCulturais\i::__('Nenhum resultado')]);
     *
     * In javascript this will be available:
     * 
     * MapasCulturais.gettext.myScript['noresults']
     * 
     * @param string $group All strings will be grouped in this property. Make this unique to avoid conflict with other scripts
     * @param array $vars Array with translated strgins with key beeing the variable name anda value beeing the translated string
     */
    public function localizeScript($group, $vars) {
        
        if (!is_string($group) || empty($group))
            throw new \Exception('localizeScript expects $group to be a string');
        
        if (!is_array($vars))
            throw new \Exception('localizeScript expects $vars to be an array');
        
        if (!isset($this->jsObject['gettext']))
            $this->jsObject['gettext'] = [];
        
        if ( isset($this->jsObject['gettext'][$group]) && is_array($this->jsObject['gettext'][$group]) ) {
            $this->jsObject['gettext'][$group] = array_merge($vars, $this->jsObject['gettext'][$group]);
        } else {
            $this->jsObject['gettext'][$group] = $vars;
        }
        
    }

    function printJsObject (string $var_name = 'Mapas', bool $print_script_tag = true) {
        $app = App::i();
        $app->applyHookBoundTo($this, 'mapas.printJsObject:before');

        $this->jsObject['route'] = [
            'route' => "{$this->controller->id}/{$this->controller->action}",
            'controllerId' => $this->controller->id,
            'action' => $this->controller->action,
            'data' => $this->controller->urlData
        ];

        $json = json_encode($this->jsObject);
        $var = "var {$var_name} = {$json};";
        if ($print_script_tag) {
            echo "\n<script type=\"text/javascript\">\n{$var}\n</script>\n";
        } else {
            echo $var;
        }
        $app->applyHookBoundTo($this, 'mapas.printJsObject:after');
    }

    function printScripts($group){
        $this->_assetManager->printScripts($group);
    }

    function printStyles($group){
        $this->_assetManager->printStyles($group);
    }

    function printDocumentMeta(){
        
        foreach($this->documentMeta as $metacfg){
            $meta = "\n <meta";
            foreach($metacfg as $prop => $val){
                $val = htmlentities((string) $val);
                $meta .= " {$prop}=\"{$val}\"";
            }
            $meta .= ' />';
            echo $meta;
        }
    }

    function resolveFilename($folder, $file){
        if(!substr($folder, -1) !== '/') $folder .= '/';

        $path = $this->path->getArrayCopy();

        foreach($path as $dir){
            if(file_exists($dir . $folder . $file)){
                return $dir . $folder . $file;
            }
        }

        return null;
    }

    function getAssetFilename($file){
        $filename = $this->resolveFilename('assets', $file);
        if(!$filename) throw new \Exception('Asset not found: ' . $file);

        return $filename;
    }

    function asset($file, $print = true, $include_hash_in_filename = true){
        $app = App::i();
        $app->applyHook('asset(' . $file . ')', [&$file]);
        $url = $this->getAssetManager()->assetUrl($file, $include_hash_in_filename);

        $app->applyHook('asset(' . $file . '):url', [&$url]);

        if($print){
            echo $url;
        }

        return $url;
    }

    function renderMarkdown($markdown){
        $app = App::i();
        $matches = [];
        if(preg_match_all('#\{\{asset:([^\}]+)\}\}#', $markdown, $matches)){
            foreach($matches[0] as $i => $tag){
                $markdown = str_replace($tag, $this->asset($matches[1][$i], false), $markdown);
            }
        }

        if(method_exists($this, 'dict') && preg_match_all('#\{\{dict:([^\}]+)\}\}#', $markdown, $matches)){
            foreach($matches[0] as $i => $tag){
                $markdown = str_replace($tag, $this->dict(trim($matches[1][$i]), false), $markdown);
            }
        }

        if(preg_match_all('#\{\{downloads:([^\}]+)\}\}#', $markdown, $matches)){
            $subsite = $app->getCurrentSubsite();
            $files = $subsite->getFiles('downloads');
            if($subsite) {
                foreach($matches[0] as $i => $tag){
                    foreach($files as $file) {
                        if($file->description == $matches[1][$i]) {
                            $markdown = str_replace($tag, $file->url, $markdown);
                            break;
                        }
                    }
                }
            }
        }
        $markdown = str_replace('{{baseURL}}', $app->getBaseUrl(), $markdown);
        $markdown = str_replace('{{assetURL}}', $app->getAssetUrl(), $markdown);
        return \Michelf\MarkdownExtra::defaultTransform($markdown);
    }

    function isEditable(){
        $result = $this->controller->action == 'edit' || $this->controller->action == 'create'|| $this->editable ?? false;

        App::i()->applyHookBoundTo($this, 'mapasculturais.isEditable', [&$result]);

        return $result;
    }

    function isSearch(){
        return (bool) $this->controller->id === 'site' && $this->action === 'search';
    }

    public $insideBody = false;

    function bodyBegin(){
        $this->insideBody = true;
        App::i()->applyHook('mapasculturais.body:before');
        $this->applyTemplateHook('body','begin');
    }

    function bodyEnd(){
        $this->applyTemplateHook('body','end');
        App::i()->applyHook('mapasculturais.body:after');
        $this->insideBody = false;
    }

    function bodyProperties(){
        $body_properties = [];

        foreach ($this->bodyProperties as $key => $val)
            $body_properties[] = "{$key}=\"$val\"";

        $body_properties[] = 'class="' . implode(' ', $this->bodyClasses->getArrayCopy()) . '"';

        echo implode(' ', $body_properties);
    }

    function head(){
        $app = App::i();

        $app->applyHook('mapasculturais.head');

        $this->printDocumentMeta();

    }
    
    function applyTemplateHook($name, $sufix = '', $args = []){
        $app = App::i();

        $hook = "template({$this->controller->id}.{$this->controller->action}.$name)";
        if($sufix){
            $hook .= ':' . $sufix;
        }

        if ($app->mode == APPMODE_DEVELOPMENT) {
            echo "\n<!-- TEMPLATE HOOK: $hook -->\n";
        }
        $app->applyHookBoundTo($this, $hook, $args);
    }
    
    /**
     * Replace links in text with html links
     *
     * http://stackoverflow.com/questions/1959062/how-to-add-anchor-tag-to-a-url-from-text-input
     * 
     * @param  string $text
     * @param  bool $force By default will check for isEditable and only add links if not on edit mode. Set force to true to force replace
     * @return string
     */
    function autoLinkString($text, $force = false) {
       
        if ($this->isEditable() && true !== $force)
            return $text;
        
        return preg_replace('@(http)?(s)?(://)?(([-\w]+\.)+([^\s]+)+[^,.\s])@', '<a href="http$2://$4" rel="noopener noreferrer">$1$2$3$4</a>', $text);
        
    }    

    function addRequestedEntityToJs(?string $entity_class_name = null, ?int $entity_id = null, ?Entity $entity = null, $disable_access_control = false){
        $entity_class_name = $entity_class_name ?: $this->controller->entityClassName ?? null;
        $entity_id = $entity_id ?: $this->controller->data['id'] ?? null;
        
        $_entity = $entity_class_name::getHookClassPath();

        if ($entity_class_name && $entity_id) {
            $app = App::i();

            if (!$entity) {
                $query_params = [
                    '@select' => '*', 
                    'id' => "EQ({$entity_id})", 
                    '@permissions'=>'view', 
                ];

                if($entity_class_name == EvaluationMethodConfiguration::class) {
                    unset($query_params['@permissions']);
                }

                if(property_exists ($entity_class_name, 'status')) {
                    $query_params['status'] = 'GTE(-20)'; 
                    $app->applyHookBoundTo($this, "view.requestedEntity($_entity).status", [&$query_params, $entity_class_name, $entity_id]);
                }

                if(property_exists ($entity_class_name, 'project')) {
                    $query_params['@select'] .= ',project.{name,type,files.avatar,terms,seals}';
                }

                if(property_exists ($entity_class_name, 'evaluationMethodConfiguration')) {
                    $query_params['@select'] .= ',evaluationMethodConfiguration.{opportunity.*,*}';
                }
                
                if ($entity_class_name::usesAgentRelation()) {
                    $query_params['@select'] .= ',agentRelations';
                }

                if ($entity_class_name::usesSpaceRelation()) {
                    $query_params['@select'] .= ',spaceRelations';
                }

                if ($entity_class_name == Entities\User::class) {
                    $query_params['@select'] .= ',profile.{name,type,files.avatar,terms,seals}';
                }

                $app->applyHookBoundTo($this, "view.requestedEntity($_entity).params", [&$query_params, $entity_class_name, $entity_id]);
                if($disable_access_control) {
                    $app->disableAccessControl();
                }
                
                $query = new ApiQuery($entity_class_name, $query_params);
                $query->__useDQLCache = false;

                $e = $query->findOne();
                if($disable_access_control) {
                    $app->enableAccessControl();
                }
            } else {
                $e = $entity->jsonSerialize();

            }

            if(property_exists ($entity_class_name, 'opportunity')) {
                if($entity) {
                    $opportunity = $entity->opportunity;
                } else {
                    $query = $app->em->createQuery("
                        SELECT o FROM                             
                            MapasCulturais\\Entities\\Opportunity o
                            WHERE o.id = (SELECT IDENTITY(e.opportunity) FROM $entity_class_name e WHERE e.id = :id)");
    
                    $query->setParameter('id', $e['id'] ?? 0);
                    $opportunity = $query->getSingleResult();
                }
                $e['opportunity'] = $opportunity->simplify('id,name,registrationFrom,registrationTo,type,files,terms,seals');
                if($opportunity->parent){
                    $e['opportunity']->parent = $opportunity->parent->simplify('id,name,type,files,terms,seals');
                }
                if ($opportunity->registrationSteps) {
                    $e['opportunity']->registrationSteps = $opportunity->registrationSteps->toArray();
                }
            }
            
            if ($entity_class_name == Entities\Opportunity::class) {
                $opportunity = $app->repo("Opportunity")->find($entity_id);

                $e['registrationSteps'] = $opportunity->registrationSteps->toArray();
            }
            
            if ($entity_class_name == Entities\Agent::class) {
                $owner_prop = 'parent';
                if (!$e['parent']) {
                    $query = $app->em->createQuery("
                        SELECT 
                            IDENTITY(e.profile) AS profile
                        FROM 
                            MapasCulturais\\Entities\\User e
                        WHERE 
                            e.id = :id");
                    $query->setParameter('id', $e['user']);
                    $result = $query->getSingleResult();
                    $e['parent'] = $result['profile'];
                }
            } else {
                $owner_prop = 'owner';
            }
                
            if($entity) {
                $owner_id = $entity->$owner_prop->id;
            } else {
                $owner_id = $e[$owner_prop] ?? false;
            }
            
            if ($owner_id) {
                $owner_query_params = [
                    '@select' => 'name, terms, files.avatar, singleUrl, shortDescription', 
                    'id' => "EQ({$owner_id})", 
                    'status' => 'GTE(-10)',
                    '@permissions'=>'view', 
                ];
                $app->applyHookBoundTo($this,"view.requestedEntity($_entity).owner.params", [&$owner_query_params, $entity_class_name, $entity_id]);
                $query = new ApiQuery(Entities\Agent::class, $owner_query_params);
                $query->__useDQLCache = false;
                $owner = $query->findOne();
                $e[$owner_prop] = $owner;
            }

            if($owner_prop != 'parent' && $entity_class_name::usesNested() && !empty($e['parent'])) {
                $parent_query_params = [
                    '@select' => 'name, terms, files.avatar, singleUrl, shortDescription', 
                    'id' => "EQ({$e['parent']})", 
                    'status' => 'GTE(-10)',
                    '@permissions'=>'view', 
                ];
                $app->applyHookBoundTo($this,"view.requestedEntity($_entity).parent.params", [&$parent_query_params, $entity_class_name, $entity_id]);
                $query = new ApiQuery($entity_class_name, $parent_query_params);
                $query->__useDQLCache = false;
                $parent = $query->findOne();
                $e['parent'] = $parent;
            }
            
            $e['controllerId'] = $app->getControllerIdByEntity($entity_class_name);

            // adiciona as permissões do usuário sobre a entidade:
            if ($entity_class_name::usesPermissionCache()) {
                if(!$entity instanceof $entity_class_name) {
                    $entity = $app->repo($entity_class_name)->find($entity_id);
                }
                $e['currentUserPermissions'] = $entity->getUserPermissions();
            }

            if ($profile_id = $e['profile']['id'] ?? false) {
                $profile = $app->repo(Agent::class)->find($profile_id);
                $e['profile']['currentUserPermissions'] = $profile->getUserPermissions();
            }

            $request_entity = $this->controller->requestedEntity;
            
            if($entity_class_name == Registration::class) {
                $meta = $request_entity->jsonSerialize();
                foreach($meta as $field => $value) {
                    if (str_starts_with($field, "field_")) {
                        $e[$field] = $value;
                    }
                }

                if(method_exists($entity_class_name, 'getAgentSealRelations')) {
                    $e['seals'] = $request_entity->agentSealRelations;
                }
            }

            if(method_exists($entity_class_name, 'getLockedFields')) {
                $e['__lockedFields'] = $request_entity->lockedFields;
            }

            if(method_exists($entity_class_name, 'getLockedFieldSeals')) {
                $e['__lockedFieldSeals'] = $request_entity->lockedFieldSeals;
            }

            $app->applyHookBoundTo($this, "view.requestedEntity($_entity).result", [&$e, $entity_class_name, $entity_id]);
            $this->jsObject['requestedEntity'] = $e;
        }
    }
}