<?php
namespace MapasCulturais;

/**
 * This is the base class to the API response generator classes
 *
 * @property-read string $contentType The content type
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
     * @param array $data
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
     */
    abstract protected function getContentType();

    abstract protected function _outputArray(array $data, $singular_object_name = 'entity', $plural_object_name = 'entities');

    abstract protected function _outputItem($data, $object_name = 'entity');

    abstract protected function _outputError($data);
}
