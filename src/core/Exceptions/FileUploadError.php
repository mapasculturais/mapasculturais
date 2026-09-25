<?php
namespace MapasCulturais\Exceptions;

/**
 * Exceção lançada quando ocorre um erro no upload de arquivo
 * 
 * Esta exceção é usada para indicar problemas durante o upload
 * de arquivos, como tamanho excedido ou outros erros do sistema.
 * 
 * @property-read string $group Grupo do arquivo
 * @property-read int $errorCode Código de erro do upload
 * 
 * @package MapasCulturais\Exceptions
 */
class FileUploadError extends \Exception{
    use \MapasCulturais\Traits\MagicGetter;

    /**
     * @var string Grupo do arquivo
     * @access protected
     */
    protected $group;

    /**
     * @var int Código de erro do upload
     * @access protected
     */
    protected $errorCode;

    /**
     * Construtor da exceção
     * 
     * @param string $group Grupo do arquivo
     * @param int $errorCode Código de erro do upload
     */
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
