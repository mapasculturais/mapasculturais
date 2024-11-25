<?php
namespace MapasCulturais\Definitions;

use DateTime;
use InvalidArgumentException;
use MapasCulturais\App;

/**
 * This class defines an Entity Metadata.
 *
 * @property-read string $key Metadata Key
 * @property-read mixed $default_value The metadata default value
 * @property-read string $label The metadata label
 * @property-read string $type The metadata input type
 * @property-read boolean $is_unique The value of metadata must be unique for the same entity?
 * @property-read string $is_unique_error_message The is_unique error message
 * @property-read boolean $is_required Is this metadata required?
 * @property-read array $config The metadata configuration. This will be extracted to the input
 */
class Metadata extends \MapasCulturais\Definition{

    /**
     * Metadata Key.
     * @var string
     */
    public $key;

    /**
     * The metadata default value.
     * @var mixed
     */
    public $default_value;

    /**
     * The metadata label.
     * @var string
     */
    public $label;

    /**
     * The metadata input type.
     * @var string
     */
    public $type;

    /**
     * The value of metadata must be unique for the same entity?
     * @var boolean
     */
    public $is_unique = false;

    /**
     * The is_unique error message.
     * @var string
     */
    public $is_unique_error_message = '';

    /**
     * Is this metadata required?
     * @var boolean
     */
    public $is_required = false;

    /**
     * The is_required error message
     * @var string
     */
    public $is_required_error_message = '';

    /**
     * Array of validations where the key is a Respect/Validation call and the value is a error message.
     * @example to validate a positive integet the key must be 'v::intVal()->positive()'
     * @var array
     */
    public $_validations= [];


    public $private = false;
    /**
     * The metadata configuration
     * @var array
     */
    public $config = [];

    public $serialize = null;

    public $unserialize = null;

    public $available_for_opportunities = false;

    public $field_type;

    public array $options = [];

    public bool $numericKeyValueOptions = false;

    public bool $readonly = false;

    /**
     * Creates a new Metadata Definition.
     *
     * To the new Metadata Definition take effects, you need register them in to the application.
     *
     * <code>
     * /**
     *  * $config example
     * new \MapasCulturais\Definitions\Metadata('age', array(
     *      'label' => 'Your Age',
     *      'type' => 'text',
     *      'validations' => array(
     *          'required' => 'You must inform your age',
     *          'v::intVal()->min(18)' => 'You must be older than 18'
     *      )
     * ));
     * </code>
     *
     * @param string $key the key of the metadata
     * @param array $config the configuration.
     *
     * @see \MapasCulturais\Validator
     * @see https://github.com/Respect/Validation
     */
    function __construct($key, $config) {
        $this->key = $key;

        $this->default_value = $config['default_value'] ?? $config['default'] ?? null;

        $this->label = $config['label'];

        $this->type = key_exists('type', $config) ? $config['type'] : 'string';

        $this->is_unique = key_exists('validations', $config) && key_exists('unique', $config['validations']);

        $this->private = key_exists('private', $config) ? $config['private'] : false;

        $this->available_for_opportunities = key_exists('available_for_opportunities', $config) ? $config['available_for_opportunities'] : false;

        $this->field_type = key_exists('field_type', $config) ? $config['field_type'] : $this->type;

        if ($this->field_type === 'string') {
            $this->field_type = 'text'; 
        }

        if($this->is_unique) {
            $this->is_unique_error_message = $config['validations']['unique'];
            unset($config['validations']['unique']);
        }

        $this->is_required = key_exists('validations', $config) && key_exists('required', $config['validations']);
        if($this->is_required){
            $this->is_required_error_message = $config['validations']['required'];
            unset($config['validations']['required']);
        }

        $this->_validations = key_exists('validations', $config) && is_array($config['validations']) ? $config['validations'] : [];

        $this->numericKeyValueOptions = $config['numericKeyValueOptions'] ?? false;

        $this->readonly = $config['readonly'] ?? false;

        if (isset($config['options']) && is_array($config['options'])) {
            $new_array = [];
            foreach ($config['options'] as $k => $value) {

                if (is_int($k) && !$this->numericKeyValueOptions) {
                    $k = $value;
                }
                $new_array[$k] = $value;
            }

            $config['options'] = $new_array;
            $this->options = $new_array;
        }

        $this->serialize = $config['serialize'] ?? $this->getDefaultSerializer();
        $this->unserialize = $config['unserialize'] ?? $this->getDefaultUnserializer();
        
        $this->config = $config;
    }

    function getDefaultSerializer() {
        $serializers = [
            'boolean' => function($value) {
                if(is_null($value)) { return null; }
                return $value ? '1' : '0';
            },
            'json' => function($value) {
                if(is_null($value)) { return null; }
                return json_encode($value);
            },
            'object' => function ($value) {
                if($value) {
                    $value = (object) $value;
                }
                return json_encode($value);
            },
            'array' => function ($value) {
                if($value) {
                    $value = (array) $value;
                }
                return json_encode($value);
            },
            'entity' => function($value) {
                if ($value instanceof \MapasCulturais\Entity) {
                    return (string) $value;
                } else {
                    return null;
                }
            },
            'DateTime' => function ($value) {
                if(is_null($value)) { return null; }
                if ($value instanceof DateTime) {
                    return $value->format('Y-m-d H:i:s');
                } else if (is_string($value)) {
                    return (new DateTime($value))->format('Y-m-d H:i:s');
                } else {
                    throw new InvalidArgumentException('value must be a DateTime or a date time string');
                }
            },
            'multiselect' => function($value){
                return json_encode($value);
            },
            'location' => function($value) {
                return json_encode($value);
            },
            'bankFields' => function($value){
                return json_encode($value);
            },
            'municipio' => function($value) {
                return json_encode($value);
            }
        ];

        $app = App::i();

        $serializer = $serializers[$this->type] ?? null;

        $app->applyHookBoundTo($this, "metadata({$this->type}).serializer", [&$serializer, &$serializers]);

        return $serializer;
    }

    function getDefaultUnserializer() {
        $app = App::i();
        $unserializers = [
            'boolean' => function($value) {
                return is_null($value) ? null : (bool) $value;
            },
            'integer' => function($value) {
                return is_null($value) ? null : (int) $value;
            },
            'int' => function($value) {
                return is_null($value) ? null : (int) $value;
            },
            'numeric' => function($value) {
                return is_null($value) ? null : (float) $value;
            },
            'number' => function($value) {
                return is_null($value) ? null : (float) $value;
            },
            'location' => function($value) {
                return is_null($value) ? null : json_decode($value);
            },
            'municipio' => function($value) {
                return is_null($value) ? null : json_decode($value);
            },
            'json' => function($value) {
                return is_null($value) ? null : json_decode($value);
            },
            'object' => function($value) {
                return is_null($value) ? null : (object) json_decode($value);
            },
            'array' => function($value) {
                return is_null($value) ? null : (array) json_decode($value);
            },
            'entity' => function($value) use ($app) {
                if (preg_match('#^((\\\?[a-z]\w*)+):(\d+)$#i', $value, $matches)) {
                    $class = $matches[1];
                    $id = $matches[3];
                    return $app->repo($class)->find($id);
                }
                return is_null($value) ? null : (array) json_decode($value);
            },
            'bankFields' => function($value) {
                return is_null($value) ? null : json_decode($value);
            },
            'DateTime' => function($value) {
                if ($value) {
                    return new DateTime($value);
                } else {
                    return $value;
                }
            },
            'multiselect' => function($value){
                $result = is_null($value) ? null : json_decode($value, true);

                if($value && !is_array($result) && ($temp_result = explode(';', $value))) {
                    $result = $temp_result;
                }

                return $result;
            }
        ];

        $app = App::i();

        $unserializer = $unserializers[$this->type] ?? null;

        $app->applyHookBoundTo($this, "metadata({$this->type}).unserializer", [&$unserializer, &$unserializers]);

        return $unserializer;
    }

    /**
     * Validates the value with the defined validation rules.
     *
     * @param mixed $value
     *
     * @return bool|array true if the value is valid or an array of errors
     */
    function validate(\MapasCulturais\Entity $entity, $value){
        $errors = [];

        if($this->is_required && (is_null($value) || $value === [])){
            $errors[] = $this->is_required_error_message;

        }elseif(!is_null($value)){
            foreach($this->_validations as $validation => $message){
                $ok = true;

                if(strpos($validation,'v::') === 0){
                    $validation = str_replace('v::', 'Respect\Validation\Validator::', $validation);
                    eval('$ok = ' . $validation . '->validate($value);');
                }else{
                    eval('$ok = ' . $validation . ';');
                }

                if(!$ok)
                    $errors[] = $message;
            }

            if(!$errors && $this->is_unique && !$this->validateUniqueValue($entity, $value))
                $errors[] = $this->is_unique_error_message;

        }

        return $errors ? $errors : true;

    }

    /**
     * Verify that there is no other metadata with the same value and key for the same entity class.
     *
     * @param \MapasCulturais\Entity $owner the owner of the metadata value
     * @param type $value the value to check.
     *
     * @return bool true if there is no metadata with the same value, false otherwise.
     */
    protected function validateUniqueValue(\MapasCulturais\Entity $owner, $value){
        $app = \MapasCulturais\App::i();

        $owner_class = $owner->className;


        if(class_exists($owner_class . 'Meta')){
            if ($owner->isNew()) {
                $q = $app->em->createQuery("SELECT COUNT(m) FROM {$owner_class}Meta m WHERE m.key = :key AND m.value = :value");
    
                $q->setParameters(['key' => $this->key, 'value' => $value]);
            } else {
                $q = $app->em->createQuery("SELECT COUNT(m) FROM {$owner_class}Meta m WHERE m.key = :key AND m.value = :value AND m.owner != :owner");
    
                $q->setParameters(['key' => $this->key, 'value' => $value, 'owner' => $owner]);
            }

        }else{
            $q = $app->em->createQuery("SELECT COUNT(m) FROM \MapasCulturais\Entities\Metadata m WHERE m.key = :key AND m.value = :value AND m.ownerType :ownerType AND m.ownerId != :ownerId");

            $q->setParameters(['key' => $this->key, 'value' => $value, 'ownerType' => $owner_class, 'ownerId' => $owner->id]);
        }

        return !$q->getSingleScalarResult();
    }


    /**
     * Returns the metadata of this metadata definition.
     *
     * <code>
     * // example of a select metadata
     * array(
     *     'label' => 'A select metadata',
     *     'required' => false,
     *     'select' => 'select',
     *     'length' => 255,
     *     'options' => array(
     *         'a value' => 'A Label',
     *         'another value' => 'Another Label'
     *     )
     * )
     *
     * // example of a string metadata
     * array(
     *     'label' => 'A string metadata',
     *     'required' => true,
     *     'select' => 'string',
     *     'length' => null
     * )
     * </code>
     *
     * @return array array with keys 'required', 'type', 'length', 'options' (if exists) and 'label' (if exists')
     */
    function getMetadata(){
        $result = [
            'required'  => $this->is_required,
            'type' => $this->type,
            'length' => key_exists('length', $this->config) ? $this->config['length'] : null,
            'private' => $this->private,
            'available_for_opportunities' => $this->available_for_opportunities,
            'field_type' => $this->field_type,
        ];

        if($this->options){
            $result['options'] = $this->options;
            $result['optionsOrder'] = array_keys($this->options);
            $result['numericKeyValueOptions'] = $this->numericKeyValueOptions;
        }

        foreach($this->config as $key => $val) {
            if (!isset($result[$key])) {
                $result[$key] = $val;
            }
        }

        return $result;
    }
}
