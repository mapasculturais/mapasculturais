<?php
namespace MapasCulturais;

/**
 * This is the base class of API output implementations.
 *
 * @property-read string $contentType The content type
 * 
 * 
 * @hook api.response({API_OUTPUT_ID}).error:before
 * @hook api.response({API_OUTPUT_ID}).error:after
 * @hook api.response({API_OUTPUT_ID}).item({$singular_object_name}):before
 * @hook api.response({API_OUTPUT_ID}).item({$singular_object_name}):after
 * @hook api.response({API_OUTPUT_ID}).array({$singular_object_name}):before
 * @hook api.response({API_OUTPUT_ID}).array({$singular_object_name}):after
 */
abstract class ApiOutput{
    use Traits\Singleton,
        Traits\MagicGetter;

    protected $hookClassName = '';

    protected function __construct() {
        $this->hookClassName = App::i()->getRegisteredApiOutputId($this);
    }

    /**
     * Outputs an error data
     *
     * @param mixed $data
     * 
     * @hook **api.response({API_OUTPUT_ID}).error:before**
     * @hook **api.response({API_OUTPUT_ID}).error:after**
     * 
     */
    public function outputError($data){
        $app = App::i();

        $hook_data = ['data' => $data];

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).error:before", $hook_data);

        $app->contentType($this->contentType);

        ob_start();
        $this->_outputError($data);
        $output = ob_get_clean();

        $hook_data['output'] = $output;

        $app->applyHookBoundTo($this, "api.response.error:after", $hook_data);
        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).error:after", $hook_data);

        echo $output;

        $app->stop();
    }

    /**
     * Outputs a single item
     * 
     * @param mixed $data
     * @param string $singular_object_name
     * @param string $plural_object_name
     * 
     * @hook **api.response({API_OUTPUT_ID}).item({$singular_object_name}):before**
     * @hook **api.response({API_OUTPUT_ID}).item({$singular_object_name}):after**
     */
    public function outputItem($data, $singular_object_name = 'entity', $plural_object_name = 'entities'){
        $app = App::i();

        $hook_data = [
            'data' => $data,
            'singular_object_name' => $singular_object_name,
            'plural_object_name' => $plural_object_name
        ];

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).item({$singular_object_name}):before", $hook_data);

        $app->contentType($this->contentType);

        ob_start();
        $this->_outputItem($data, $singular_object_name, $plural_object_name);
        $output = ob_get_clean();

        $hook_data['output'] = $output;

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).item({$singular_object_name}):after", $hook_data);

        echo $output;

        $app->stop();
    }

    /**
     * Outputs an array of items
     * 
     * @param array $data
     * @param string $singular_object_name
     * @param string $plural_object_name
     * 
     * @hook **api.response({API_OUTPUT_ID}).array({$singular_object_name}):before**
     * @hook **api.response({API_OUTPUT_ID}).array({$singular_object_name}):after**
     */
    public function outputArray(array $data, $singular_object_name = 'entity', $plural_object_name = 'entities'){
        $app = App::i();

        $hook_data = [
            'data' => $data,
            'singular_object_name' => $singular_object_name,
            'plural_object_name' => $plural_object_name
        ];

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).array({$plural_object_name}):before", $hook_data);

        $app->contentType($this->contentType);

        ob_start();
        $this->_outputArray($data, $singular_object_name, $plural_object_name);
        $output = ob_get_clean();

        $hook_data['output'] = $output;

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).array({$plural_object_name}):after", $hook_data);

        echo $output;

        $app->stop();
    }

    /**
     * Returns the content type of this response generator
     *
     * @return string the content type
     * 
     * @example return the string **application/json**
     */
    abstract protected function getContentType();

    /**
     * Returns the content to be printed
     * 
     * @param array $data
     * @param string $singular_object_name
     * @param string $plural_object_name
     */
    abstract protected function _outputArray(array $data, $singular_object_name = 'entity', $plural_object_name = 'entities');

    /**
     * Returns the content to be printed
     * 
     * @param mixed $data
     * @param string $object_name
     */
    abstract protected function _outputItem($data, $object_name = 'entity');

    /**
     * Returns the error message to be printed
     * 
     * @param mixed $data
     */
    abstract protected function _outputError($data);
}
