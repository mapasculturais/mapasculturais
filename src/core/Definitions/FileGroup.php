<?php
namespace MapasCulturais\Definitions;

use \MapasCulturais\App;
use MapasCulturais\i;

class FileGroup extends \MapasCulturais\Definition{
    use \MapasCulturais\Traits\MagicGetter;

    public $name = '';

    public $unique = false;

    public $maxFiles = null;

    public $errorMessage = '';

    public $_validations = [];
    
    public $private = false;

    public $_blockedExtensions = [];

    /**
     *
     * @param string $name The group name
     * @param array $validations An array with regex to validate file mime type
     * @param string $error_message The error message to display if the file mime type is not valid
     * @param bool $unique If this group contains just one file for each owner. If this is set to true the uploaded file always replaces the existent file. 
     * @param null|int $max_files Maximum files in this group.
     * @param bool $private Wether files in this group are private and can only be accessed by user with the right permissions.
     */
    function __construct($name, array $validations = [], $error_message = '', $unique = false, $max_files = null, $private = false, array $blocked_extensions = []) {
        $this->name = $name;
        $this->_validations = $validations;
        $this->errorMessage = $error_message;
        $this->unique = $unique;
        $this->maxFiles = $max_files;
        $this->private = $private;
        $this->_blockedExtensions = !empty($blocked_extensions) ? $blocked_extensions : self::getDefaultBlockedExtensions();
    }

    static function getDefaultBlockedExtensions(): array {
        $app = App::i();
        $config_value = $app->config['app.not_allowed_extensions'] ?? '';
        if (is_string($config_value) && $config_value !== '') {
            return array_map('trim', explode(',', $config_value));
        }
        if (is_array($config_value)) {
            return $config_value;
        }
        return [];
    }

    function getExtensionError(\MapasCulturais\Entities\File $file): string {
        $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
        if ($ext === '') {
            return '';
        }
        foreach ($this->_blockedExtensions as $blocked) {
            if ($ext === strtolower(trim($blocked))) {
                return i::__('Extensão de arquivo não permitida.');
            }
        }
        return '';
    }

    static function getDefaultAllowedMimeTypes(): array {
        $app = App::i();
        $value = $app->config['app.default_allowed_mime_types'] ?? [];
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }
    
    /**
     * Validates the file and if it is not valid returns the error message
     *
     */
    function getError(\MapasCulturais\Entities\File $file): string {
        $validations = !empty($this->_validations)
            ? $this->_validations
            : self::getDefaultAllowedMimeTypes();

        $ok = false;
        foreach($validations as $validation){
            if(preg_match("#$validation#i", $file->mimeType)){
                $ok = true;
                break;
            }
        }

        if (!$ok) {
            return $this->errorMessage ?: i::__('Tipo de arquivo não permitido.');
        }
        return '';
    }
}
