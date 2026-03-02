<?php
namespace MapasCulturais\Definitions;

use DateTime;
use InvalidArgumentException;
use MapasCulturais\App;

/**
 * Define um Metadado de Entidade.
 *
 * Esta classe define a estrutura e comportamento de um metadado que pode ser
 * associado a entidades do sistema, incluindo validações, serialização e
 * configurações de exibição.
 *
 * @property-read string $key Chave do Metadado
 * @property-read mixed $default_value Valor padrão do metadado
 * @property-read string $label Rótulo do metadado
 * @property-read string $type Tipo de entrada do metadado
 * @property-read boolean $is_unique O valor do metadado deve ser único para a mesma entidade?
 * @property-read string $is_unique_error_message Mensagem de erro para unicidade
 * @property-read boolean $is_required Este metadado é obrigatório?
 * @property-read array $config Configuração do metadado. Será extraída para o input
 * 
 * @package MapasCulturais\Definitions
 */
class Metadata extends \MapasCulturais\Definition{

    /**
     * Chave do Metadado.
     * @var string
     */
    public $key;

    /**
     * Valor padrão do metadado.
     * @var mixed
     */
    public $default_value;

    /**
     * Rótulo do metadado.
     * @var string
     */
    public $label;

    /**
     * Tipo de entrada do metadado.
     * @var string
     */
    public $type;

    /**
     * O valor do metadado deve ser único para a mesma entidade?
     * @var boolean
     */
    public $is_unique = false;

    /**
     * Mensagem de erro para unicidade.
     * @var string
     */
    public $is_unique_error_message = '';

    /**
     * Este metadado é obrigatório?
     * @var boolean
     */
    public $is_required = false;

    /**
     * Mensagem de erro para obrigatoriedade
     * @var string
     */
    public $is_required_error_message = '';

    /**
     * Array de validações onde a chave é uma chamada Respect/Validation e o valor é uma mensagem de erro.
     * @example para validar um inteiro positivo a chave deve ser 'v::intVal()->positive()'
     * @var array
     */
    public $_validations= [];


    /**
     * Indica se o metadado é privado (não visível publicamente)
     * @var boolean
     */
    public $private = false;
    
    /**
     * Configuração do metadado
     * @var array
     */
    public $config = [];

    /**
     * Função de serialização do valor
     * @var callable|null
     */
    public $serialize = null;

    /**
     * Função de desserialização do valor
     * @var callable|null
     */
    public $unserialize = null;

    /**
     * Disponível para oportunidades
     * @var boolean
     */
    public $available_for_opportunities = false;

    /**
     * Tipo de campo (pode ser diferente do tipo de entrada)
     * @var string
     */
    public $field_type;

    /**
     * Opções para campos do tipo select
     * @var array
     */
    public array $options = [];

    /**
     * Usar chaves numéricas para opções
     * @var bool
     */
    public bool $numericKeyValueOptions = false;

    /**
     * Campo somente leitura
     * @var bool
     */
    public bool $readonly = false;

    /**
     * Dados sensíveis (tratamento especial)
     * @var bool
     */
    public bool $sensitive = false;

    /**
     * Cria uma nova Definição de Metadado.
     *
     * Para que a nova Definição de Metadado tenha efeito, você precisa registrá-la na aplicação.
     *
     * <code>
     * // Exemplo de configuração
     * new \MapasCulturais\Definitions\Metadata('idade', array(
     *      'label' => 'Sua Idade',
     *      'type' => 'text',
     *      'validations' => array(
     *          'required' => 'Você deve informar sua idade',
     *          'v::intVal()->min(18)' => 'Você deve ter mais de 18 anos'
     *      )
     * ));
     * </code>
     *
     * @param string $key a chave do metadado
     * @param array $config a configuração.
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
        
        $this->sensitive = $config['sensitive'] ?? false;

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

    /**
     * Obtém o serializador padrão baseado no tipo do metadado
     * 
     * @return callable|null Função de serialização
     */
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
                $value = (array) $value;
                $value = array_filter($value);
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

    /**
     * Obtém o desserializador padrão baseado no tipo do metadado
     * 
     * @return callable|null Função de desserialização
     */
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
                if (is_null($value)) {
                    return null;
                } else if (is_string($value)) {
                    return (object) json_decode($value);
                } else {
                    return $value;
                }
            },
            'array' => function($value) {
                if (is_null($value)) {
                    return null;
                } else if (is_string($value)) {
                    return (array) json_decode($value);
                } else {
                    return $value;
                }
            },
            'entity' => function($value) use ($app) {
                if (is_string($value) && preg_match('#^((\\\?[a-z]\w*)+):(\d+)$#i', $value, $matches)) {
                    $class = $matches[1];
                    $id = $matches[3];
                    return $app->repo($class)->find($id);
                }
                return $value;
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
                $result = is_null($value) ? [] : json_decode($value, true);

                if($value && !is_array($result) && ($temp_result = explode(';', $value))) {
                    $result = $temp_result;
                }

                $result = array_filter($result, function($v){
                    return !is_null($v) && $v !== 'null';
                });

                return $result;
            }
        ];

        $app = App::i();

        $unserializer = $unserializers[$this->type] ?? null;

        $app->applyHookBoundTo($this, "metadata({$this->type}).unserializer", [&$unserializer, &$unserializers]);

        return $unserializer;
    }

    /**
     * Verifica se a validação do metadado deve ser executada, mesmo se o valor estiver vazio
     * 
     * @param \MapasCulturais\Entity $entity Entidade dona do metadado
     * @param mixed $value Valor do metadado
     * @return string|false Mensagem de erro de validação, se a validação for necessária, ou false caso contrário
     */
    function shouldValidate(\MapasCulturais\Entity $entity, $value) {
        if ($this->is_required) {
            return $this->is_required_error_message;
        }

        if (!empty($this->config['should_validate']) && is_callable($this->config['should_validate'])) {
            if ($error_message = $this->config['should_validate']($entity, $value)) {
                return $error_message;
            }
        }

        return false;
    }

    /**
     * Valida o valor com as regras de validação definidas.
     *
     * @param \MapasCulturais\Entity $entity Entidade dona do metadado
     * @param mixed $value Valor a ser validado
     *
     * @return bool|array true se o valor for válido ou um array de erros
     */
    function validate(\MapasCulturais\Entity $entity, $value){
        $errors = [];

        if(is_null($value) || $value === []){
            if ($message = $this->shouldValidate($entity, $value)) {
                $errors[] = $message;
            }
        }else{
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

            if(!$errors && $this->is_unique && !$this->validateUniqueValue($entity, $value)){
                $errors[] = $this->is_unique_error_message;
            }
        }

        return $errors ? $errors : true;

    }

    /**
     * Verifica se não há outro metadado com o mesmo valor e chave para a mesma classe de entidade.
     *
     * @param \MapasCulturais\Entity $owner o dono do valor do metadado
     * @param mixed $value o valor a verificar.
     *
     * @return bool true se não houver metadado com o mesmo valor, false caso contrário.
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
     * Retorna os metadados desta definição de metadado.
     *
     * <code>
     * // exemplo de um metadado select
     * array(
     *     'label' => 'Um metadado select',
     *     'required' => false,
     *     'select' => 'select',
     *     'length' => 255,
     *     'options' => array(
     *         'um valor' => 'Um Rótulo',
     *         'outro valor' => 'Outro Rótulo'
     *     )
     * )
     *
     * // exemplo de um metadado string
     * array(
     *     'label' => 'Um metadado string',
     *     'required' => true,
     *     'select' => 'string',
     *     'length' => null
     * )
     * </code>
     *
     * @return array array com chaves 'required', 'type', 'length', 'options' (se existir) e 'label' (se existir)
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
