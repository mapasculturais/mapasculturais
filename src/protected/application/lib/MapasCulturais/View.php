<?php
namespace MapasCulturais;

use MapasCulturais\App;

/**
 * This is the default MapasCulturais View class. It extends the \Slim\View class adding a layout layer and the option to render the template partially.
 *
 * When rendering, the template can access view object whith the $this variable and the controller that call the render/partial methiod with $this->controller.
 *
 * @property \MapasCulturais\View $layout The layout to use when rendering the template.
 * @property \MapasCulturais\Controller $controller The controller that call the render / partial
 *
 * @hook **view.render:before ($template_name)** - executed before the render of the template and the layout
 * @hook **view.render({$template_name}):before ($template_name)** - executed before the render of the template and the layout
 * @hook **view.partial:before ($template_name)** - executed before the template render.
 * @hook **view.partial({$template_name}):before ($template_name)** - executed before the template render.
 * @hook **view.partial:after ($template_name, $html)** - executed after the template render.
 * @hook **view.partial({$template_name}):before ($template_name, $html)** - executed after the template render.
 * @hook **view.render:after ($template_name, $html)** - executed after the render of the template and the layout
 * @hook **view.render({$template_name}):before ($template_name, $html)** - executed after the render of the template and the layout
 */
class View extends \Slim\View {
    use Traits\MagicGetter,
        Traits\MagicSetter;

    /**
     * The controller that is using this view object.
     * @var \MapasCulturais\Controller
     */
    protected $_controller;

    /**
     * The layout to use when rendering the template.
     * @var string
     */
    protected $_layout = 'default';

    /**
     * The template that this view is rendering.
     * @var string
     */
    protected $template = '';

    /**
     * When to render the template partially
     * @var bool
     */
    protected $_partial = false;
    
    /**
     * CSS Classes to print in body tag
     * @var array 
     */
    protected $bodyClasses = null;
    
    /**
     * Properties of body tag
     * @var array 
     */
    protected $bodyProperties =  null;

    public function __construct() {
        parent::__construct();
        
        $this->bodyClasses = new \ArrayObject();
        $this->bodyProperties = new \ArrayObject();
    }
    /**
     * Sets partial property.
     *
     * Use this passing true when you want to render the template without the layout.
     *
     * @param bool $val
     */
    public function setPartial($val){
        $this->_partial = $val;
    }

    /**
     * Sets the layout property.
     * @param string $name
     */
    public function setLayout($name){
        $this->_layout = $name;
    }

    /**
     * Sets the controller property.
     *
     * @param \MapasCulturais\Controller $controller the controller.
     */
    public function setController(\MapasCulturais\Controller $controller){
        $this->_controller = $controller;
    }

    /**
     * Returns the controller that is using this view object (call render method).
     * @return \MapasCulturais\Controller the controller that call render method.
     */
    public function getController(){
        return $this->_controller;
    }

    /**
     * Render the template.
     *
     * Inside the template the view object is the $this variable and the controller that call the template is $this->controller.
     *
     * If the property "partial" is setted to true the template will be rendered without the layout.
     *
     * @param string $template the template name.
     * @return string The rendered template
     */
    public function render($template, $data = null){
        $this->template = $template;

        if($this->_partial)
            return $this->partialRender ($template, $this->data);
        else
            return $this->fullRender ($template);
    }

    /**
     * Render the template with the layout.
     *
     * To change the layout that will be used set the property "layout" of the this object inside the template file.
     * Inside the template file, the view object is the $this variable, so to set the layout you can do: $this->layout = 'my-layout';
     *
     * This method extracts the property "data" array to make the variables accessible inside the template.
     *
     * @param string $template the template to render
     *
     * @hook **view.render:before ($template_name)** - executed before the render of the template and the layout
     * @hook **view.render({$template_name}):before ($template_name)** - executed before the render of the template and the layout
     * @hook **view.render:after ($template_name, $html)** - executed after the render of the template and the layout
     * @hook **view.render({$template_name}):before ($template_name, $html)** - executed after the render of the template and the layout
     *
     * @return string The rendered template.
     */
    public function fullRender($template){
        $app = App::i();

        $baseURL = $app->baseUrl;
        $assetURL = $app->assetUrl;

        foreach($this->data->keys() as $k)
            $$k = $this->data->get($k);
        
        
        if ($this->controller){ 
            $this->bodyClasses[] = "controller-{$this->controller->id}";
            $this->bodyClasses[] = "action-{$this->controller->action}";
        }
	if (isset($entity)) 
            $this->bodyClasses[] = 'entity';

        // render the template
        $templatePath = $this->templatesDirectory . '/' . $template;
        if(strtolower(substr($templatePath, -4)) !== '.php')
                $templatePath .= '.php';

        $template_name = substr(preg_replace('#^'.$this->templatesDirectory.'/?#', '', $templatePath),0,-4);

        $app->applyHookBoundTo($this, 'view.render(' . $template_name . '):before', array('template' => $template_name));

        $TEMPLATE_CONTENT = $this->partialRender($template_name, $this->data);

        // render the layout with template
        $layoutPath = $app->config['path.layouts'] . '/' . $this->_layout;
        if(strtolower(substr($layoutPath, -4)) !== '.php')
                $layoutPath .= '.php';

        ob_start(function($output){
            return $output;
        });

        include $layoutPath;

        $html = ob_get_clean();

        $app->applyHookBoundTo($this, 'view.render(' . $template_name . '):after', array('template' => $template_name, 'html' => &$html));

        return $html;
    }

    /**
     * Render the template without the layout.
     *
     * This method is called when the property "partial" was setted to true before the render method is called.
     *
     * This method extracts the data array to make the variables accessible inside the template.
     *
     * @param string $template the template to render
     * @param array $data the data to be passed to template.
     *
     * @hook **view.partial:before ($template_name)** - executed before the template render.
     * @hook **view.partial({$template_name}):before ($template_name)** - executed before the template render.
     * @hook **view.partial:after ($template_name, $html)** - executed after the template render.
     * @hook **view.partial({$template_name}):before ($template_name, $html)** - executed after the template render.
     *
     * @return string The rendered template.
     */
    public function partialRender($template, $data = array()){
        $app = App::i();

        $baseURL = $app->baseUrl;
        $assetURL = $app->assetUrl;

        if(is_array($data))
            extract($data);
        elseif($data instanceof \Slim\Helper\Set)
            foreach($this->data->keys() as $k)
                $$k = $this->data->get($k);

        // render the template
        $templatePath = $this->templatesDirectory . '/' . $template;
        if(strtolower(substr($templatePath, -4)) !== '.php' && strtolower(substr($templatePath, -5)) !== '.html')
                $templatePath .= '.php';

        $template_name = substr(preg_replace('#^'.$this->templatesDirectory.'/?#', '', $templatePath),0,-4);

        $app->applyHookBoundTo($this, 'view.partial(' . $template_name . '):before', array('template' => $template_name));

        ob_start(function($output){
            return $output;
        });

        include $templatePath;

        $html = ob_get_clean();

        $app->applyHookBoundTo($this, 'view.partial(' . $template_name . '):after', array('template' => $template_name, 'html' => &$html));

        return $html;
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
    public function part($template, $data = array()){
        if(strpos($template, '/') === false)
                $template = '../layouts/parts/' . $template;

        echo $this->partialRender($template, $data);
    }
    
    function getTitle($entity = null){
        $app = App::i();
        $title = '';
        if($entity){
            $controller = $app->getControllerByEntity($entity);

            $title .= $app->getReadableName($controller->action) ? $app->getReadableName($controller->action) : '';
            $title .= $app->getReadableName($controller->id) ? ' '.$app->getReadableName($controller->id) : '';
            $title .= $entity->name ? ' '.$entity->name : '';
        }elseif($this->controller->id == 'site' && $this->controller->action === 'index'){
            $title = $app->siteName;
        }else{
            $title =$app->getReadableName($this->controller->action);
        }

        return $title;
    }
    
    function asset($file, $print = true){
        $url = App::i()->getAssetUrl() . '/' . $file;
        if($print)
            echo $url;
        
        return $url;
    }
    
    function renderMarkdown($markdown){
        $app = App::i();
        $markdown = str_replace('{{baseURL}}', $app->getBaseUrl(), $markdown);
        $markdown = str_replace('{{assetURL}}', $app->getAssetUrl(), $markdown);
        return \Michelf\MarkdownExtra::defaultTransform($markdown);
    }
}