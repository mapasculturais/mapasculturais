<?php
namespace MapasCulturais\Definitions;

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
    protected $key;

    /**
     * The metadata default value.
     * @var mixed
     */
    protected $default_value;

    /**
     * The metadata label.
     * @var string
     */
    protected $label;

    /**
     * The metadata input type.
     * @var string
     */
    protected $type;

    /**
     * The value of metadata must be unique for the same entity?
     * @var boolean
     */
    protected $is_unique = false;

    /**
     * The is_unique error message.
     * @var string
     */
    protected $is_unique_error_message = '';

    /**
     * Is this metadata required?
     * @var boolean
     */
    protected $is_required = false;

    /**
     * The is_required error message
     * @var string
     */
    protected $is_required_error_message = '';

    /**
     * Array of validations where the key is a Respect/Validation call and the value is a error message.
     * @example to validate a positive integet the key must be 'v::intVal()->positive()'
     * @var array
     */
    protected $_validations= [];


    protected $private = false;
    /**
     * The metadata configuration
     * @var array
     */
    protected $config = [];
    
    protected $serialize = null;
    
    protected $unserialize = null;

    /**
     * Creates a new Metadata Definition.
     *
     * To the new Metadata Definition take effects, you need register them in to the application.
     *
     * <code>
     * /**
     *  * $config example
     * {@*}
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

        $this->default_value = key_exists('default_value', $config) ? $config['default_value'] : null;

        $this->label = $config['label'];

        $this->type = key_exists('type', $config) ? $config['type'] : 'string';

        $this->is_unique = key_exists('validations', $config) && key_exists('unique', $config['validations']);

        $this->private = key_exists('private', $config) ? $config['private'] : false;
        
        $this->serialize = key_exists('serialize', $config) ? $config['serialize'] : null;
        $this->unserialize = key_exists('unserialize', $config) ? $config['unserialize'] : null;

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

        if (isset($config['options'])) {
            $new_array = [];
            foreach ($config['options'] as $key => $value) {
                if (!is_string($key)) {
                    $key = $value;
                }

                $new_array[$key] = $value;
            }

            $config['options'] = $new_array;
        }

        $this->config = $config;
    }

    /**
     * Validates the value with the defined validation rules.
     *
     * @param mixed $value
     *
     * @return bool|array true if the value is valid or an array of errors
     */
    function validate(\MapasCulturais\Entity $owner, $value){
        $errors = [];

        if($this->is_required && !$value){
            $errors[] = $this->is_required_error_message;

        }elseif($value){
            foreach($this->_validations as $validation => $message){
                $ok = true;
                $validation = str_replace('v::', 'MapasCulturais\Validator::', $validation);

                eval('$ok = ' . $validation . '->validate($value);');

                if(!$ok)
                    $errors[] = $message;
            }

            if(!$errors && $this->is_unique && !$this->validateUniqueValue($owner, $value))
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
            $q = $app->em->createQuery("SELECT COUNT(m) FROM {$owner_class}Meta m WHERE m.key = :key AND m.value = :value AND m.owner != :owner");

            $q->setParameters(['key' => $this->key, 'value' => $value, 'owner' => $owner]);

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
            'private' => $this->private
        ];

        if(key_exists('options', $this->config)){
            $result['options'] = $this->config['options'];
            $result['optionsOrder'] = array_keys($this->config['options']);
        }

        if(key_exists('label', $this->config)){
            $result['label'] = $this->config['label'];
        }


        if(key_exists('allowOther', $this->config)){
            $result['allowOther'] = $this->config['allowOther'];
        }


        if(key_exists('allowOtherText', $this->config)){
            $result['allowOtherText'] = $this->config['allowOtherText'];
        }



        return $result;
    }
}