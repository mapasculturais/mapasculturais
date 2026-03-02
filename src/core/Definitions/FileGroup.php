<?php
namespace MapasCulturais\Definitions;

use \MapasCulturais\App;

/**
 * Define um Grupo de Arquivos para entidades no sistema Mapas Culturais.
 *
 * Um Grupo de Arquivos é usado para agrupar arquivos relacionados a uma entidade, como imagens, documentos ou mídias.
 * Esta classe permite definir regras de validação, limites de quantidade e permissões de acesso para os arquivos.
 *
 * @property-read string $name Nome do grupo.
 * @property-read boolean $unique Indica se o grupo contém apenas um arquivo por proprietário.
 * @property-read int $maxFiles Número máximo de arquivos permitidos no grupo.
 * @property-read string $errorMessage Mensagem de erro para validação de tipo MIME.
 * @property-read boolean $private Indica se os arquivos são privados e exigem permissões especiais para acesso.
 * @property-read array $_validations Validações de tipo MIME para os arquivos do grupo.
 *
 * @package MapasCulturais\Definitions
 */
class FileGroup extends \MapasCulturais\Definition{
    use \MapasCulturais\Traits\MagicGetter;

    /**
     * Nome do grupo.
     * @var string
     */
    public $name = '';

    /**
     * Indica se o grupo contém apenas um arquivo por proprietário.
     * @var bool
     */
    public $unique = false;

    /**
     * Número máximo de arquivos permitidos no grupo.
     * @var int|null
     */
    public $maxFiles = null;

    /**
     * Mensagem de erro para validação de tipo MIME.
     * @var string
     */
    public $errorMessage = '';

    /**
     * Validações de tipo MIME para os arquivos do grupo.
     * @var array
     */
    public $_validations = [];
    
    /**
     * Indica se os arquivos são privados e exigem permissões especiais para acesso.
     * @var bool
     */
    public $private = false;

    /**
     * Construtor da classe.
     *
     * @param string $name Nome do grupo.
     * @param array $validations Array com regex para validar tipo MIME do arquivo.
     * @param string $error_message Mensagem de erro a ser exibida se o tipo MIME não for válido.
     * @param bool $unique Se true, o grupo contém apenas um arquivo por proprietário. O arquivo enviado substitui o existente.
     * @param null|int $max_files Número máximo de arquivos permitidos no grupo.
     * @param bool $private Se true, os arquivos são privados e exigem permissões especiais para acesso.
     */
    function __construct($name, array $validations = [], $error_message = '', $unique = false, $max_files = null, $private = false) {
        $this->name = $name;
        $this->_validations = $validations;
        $this->errorMessage = $error_message;
        $this->unique = $unique;
        $this->maxFiles = $max_files;
        $this->private = $private;
    }

    /**
     * Valida o tipo MIME de um arquivo e retorna uma mensagem de erro se o arquivo não for válido.
     *
     * @param \MapasCulturais\Entities\File $file Arquivo a ser validado.
     * @return string Mensagem de erro se o arquivo não for válido. Retorna uma string vazia se o arquivo for válido.
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
