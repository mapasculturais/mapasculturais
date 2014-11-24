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
            $message = "The uploaded file is larger than the allowed size.";
        }else{
            $message = 'Unexpected error.';
        }

        parent::__construct($message);
    }
}