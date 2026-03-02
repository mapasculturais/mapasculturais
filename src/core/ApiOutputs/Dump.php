<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;
use MapasCulturais;

/**
 * Saída de API em formato de dump para debug
 * 
 * Esta classe gera saídas HTML com var_dump dos dados,
 * útil para desenvolvimento e depuração.
 * 
 * @package MapasCulturais\ApiOutputs
 */
class Dump extends \MapasCulturais\ApiOutput{

    /**
     * Retorna o tipo de conteúdo HTTP para esta saída
     * 
     * @return string Tipo de conteúdo (text/html)
     */
    protected function getContentType() {
        return 'text/html';
    }


    /**
     * Gera a saída HTML com dump de um array de dados
     * 
     * @param array $data Dados a serem exibidos
     * @param string $singular_object_name Nome no singular para a entidade
     * @param string $plural_object_name Nome no plural para a entidade
     */
    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        $uriExplode = explode('/',$_SERVER['REQUEST_URI']);
        if($data && key_exists(2,$uriExplode) ){
            $singular_object_name = mb_convert_encoding($this->translate[$uriExplode[2]],"HTML-ENTITIES","UTF-8");
            $plural_object_name = $singular_object_name.'s';
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <?php if(count($data) === 1):?>
                    <title><?php echo sprintf("%s $singular_object_name encontrado.", count($data)) ?></title>
                <?php else:?>
                    <title><?php echo sprintf("%s $plural_object_name encontrados.", count($data)) ?></title>
                <?php endif?>

                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    table table th {text-align: left; white-space: nowrap; }
                </style>
            </head>
            <body>
               <?php \dump($data); ?>
            </body>
        </html>
        <?php
    }

    /**
     * Gera a saída HTML com dump de um único item
     * 
     * @param mixed $data Dados a serem exibidos
     * @param string $object_name Nome do objeto
     */
    function _outputItem($data, $object_name = 'entity') {
        \dump($data); 
    }

    /**
     * Gera a saída HTML com dump de um erro
     * 
     * @param mixed $data Dados do erro
     */
    protected function _outputError($data) {
        \dump($data);
    }
}
