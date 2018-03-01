<?php
namespace MapasCulturais\Definitions;

use \MapasCulturais\App;

/**
 * This class defines a File Group
 * 
 * @property-read string $name
 * @property-read boolean $unique
 * @property-read int $maxFiles
 */
class FileGroup extends \MapasCulturais\Definition{
    use \MapasCulturais\Traits\MagicGetter;

    protected $name = '';

    protected $unique = false;

    protected $maxFiles = null;

    protected $errorMessage = '';

    protected $_validations = [];

    /**
     *
     * @param string $name The group name
     * @param array $validations An array with regex to validate file mime type
     * @param type $error_message The error message to display if the file mime type is not valid
     * @param type $unique If this group contains just one file. If this is set to true the uploaded file always replaces the existent file.
     * @param type $max_files Maximum files in this group.
     */
    function __construct($name, array $validations = [], $error_message = '', $unique = false, $max_files = null) {
        $this->name = $name;
        $this->_validations = $validations;
        $this->errorMessage = $error_message;
        $this->unique = $unique;
        $this->maxFiles = $max_files;
    }

    /**
     * Validates the file and if it is not valid returns the error message
     *
     */
    function getError(\MapasCulturais\Entities\File $file){
        $ok = false;
        foreach($this->_validations as $validation){
            if(preg_match("#$validation#i", $file->mimeType)){
                $ok = true;
                break;
            }
        }

        return !$ok ? $this->errorMessage : '';
    }
}
