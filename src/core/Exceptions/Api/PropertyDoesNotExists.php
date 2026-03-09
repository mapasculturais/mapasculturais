<?php
namespace MapasCulturais\Exceptions\Api;

/**
 * Exceção lançada quando uma propriedade inexistente é acessada na API
 * 
 * Esta exceção é usada para indicar que uma propriedade ou campo
 * solicitado através da API não existe na entidade ou recurso.
 * 
 * @package MapasCulturais\Exceptions\Api
 */
class PropertyDoesNotExists extends \Exception{
}