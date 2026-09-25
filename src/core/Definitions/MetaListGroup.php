<?php
namespace MapasCulturais\Definitions;

/**
 * Esta classe define um Grupo de MetaList
 * 
 * @property-read string $name Nome do grupo
 * @property-read int|null $maxFiles Número máximo de arquivos
 * @property-read array $metadata Metadados do grupo
 * @property-read int|null $maxItems Número máximo de itens
 */
class MetaListGroup{
    use \MapasCulturais\Traits\MagicGetter;

    /**
     * Nome do grupo
     * @var string
     */
    public $name = '';

    /**
     * Número máximo de arquivos
     * @var int|null
     */
    public $maxFiles = null;

    /**
     * Metadados do grupo
     * @var array
     */
    public $metadata = [];

    /**
     * Número máximo de itens
     * @var int|null
     */
    public $maxItems = null;

    /**
     * Construtor da classe
     *
     * @param string $name Nome do grupo
     * @param array $metadata Metadados do grupo
     * @param int|null $max_items Número máximo de itens
     */
    function __construct($name, array $metadata = [], $max_items = null) {
        $this->name = $name;
        $this->metadata = $metadata;
        //$this->errorMessage = $error_message;
        //$this->unique = $unique;
        $this->maxItems = $max_items;
    }

    /**
     * Valida o arquivo e retorna a mensagem de erro se não for válido
     *
     * @param \MapasCulturais\Entities\MetaList $file Arquivo a ser validado
     * @return bool Sempre retorna true (método em desenvolvimento)
     */
    function getError(\MapasCulturais\Entities\MetaList $file){

        return true; //IMPORTANT!

        $ok = false;
        foreach($this->metadata as $metadata){
            foreach($metadata->validations as $validation){
                if(preg_match("#$validation#i", $file->mimeType)){
                    $ok = true;
                    break;
                }
            }
        }

        return !$ok ? $this->errorMessage : '';
    }


    /**
     * Valida o valor com as regras de validação definidas
     *
     * @param \MapasCulturais\Entity $owner Proprietário do valor
     * @param mixed $value Valor a ser validado
     * @return bool|array true se o valor for válido ou um array de erros
     */
    function validate(\MapasCulturais\Entity $owner, $value){
        $errors = [];
        if($this->is_required && (is_string($value) && trim($value) === '' || is_array($value) && count($value) === 0 || is_null($value))){
            $errors[] = $this->is_required_error_message;
        }else{
            foreach($this->_validations as $validation => $message){
                $ok = true;
                $validation = str_replace('v::', 'Respect\Validation\Validator::', $validation);

                eval('$ok = ' . $validation . '->validate($value);');

                if(!$ok)
                    $errors[] = $message;
            }
        }

        return $errors ? $errors : true;

    }
}
