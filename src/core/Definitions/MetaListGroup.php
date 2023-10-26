<?php
namespace MapasCulturais\Definitions;

/**
 * This class defines a MetaList Group
 */
class MetaListGroup{
    use \MapasCulturais\Traits\MagicGetter;

    public $name = '';

    public $maxFiles = null;

    public $metadata = [];

    public $maxItems = null;

    /**
     *
     * @param string $name The group name
     * @param array $validations An array with regex to validate file mime type
     * @param type $error_message The error message to display if the file mime type is not valid
     * @param type $unique If this group contains just one file. If this is set to true the uploaded file always replaces the existent file.
     * @param type $max_files Maximum files in this group.
     */
    function __construct($name, array $metadata = [], $max_items = null) {
        $this->name = $name;
        $this->metadata = $metadata;
        //$this->errorMessage = $error_message;
        //$this->unique = $unique;
        $this->maxItems = $max_items;
    }

    /**
     * Validates the file and if it is not valid returns the error message
     *
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
     * Validates the value with the defined validation rules.
     *
     * @param mixed $value
     *
     * @return bool|array true if the value is valid or an array of errors
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
