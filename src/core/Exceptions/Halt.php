<?php
namespace MapasCulturais\Exceptions;

/**
 * Exceção usada para interromper a execução do fluxo atual
 * 
 * Esta exceção é usada para parar a execução de um processo
 * ou fluxo de trabalho de forma controlada, sem indicar
 * necessariamente um erro.
 * 
 * @package MapasCulturais\Exceptions
 */
class Halt extends \Exception{
}