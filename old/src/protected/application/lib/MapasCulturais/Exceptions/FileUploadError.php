<?php
namespace MapasCulturais\Exceptions;

class FileUploadError extends \Exception{
    use \MapasCulturais\Traits\MagicGetter;

    protected $group;

    protected $errorCode;

    public function __construct($group, $errorCode) {
        $this->group = $group;
        $this->errorCode = $errorCode;

        if($errorCode === UPLOAD_ERR_INI_SIZE){
            $message = \MapasCulturais\i::__("O arquivo enviado é maior do que o permitido.");
        }else{
            $message = \MapasCulturais\i::__('Erro inesperado.');
        }

        parent::__construct($message);
    }
}
