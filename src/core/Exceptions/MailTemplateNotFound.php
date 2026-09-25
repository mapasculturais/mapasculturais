<?php
namespace MapasCulturais\Exceptions;

/**
 * Exceção lançada quando um template de email não é encontrado
 * 
 * Esta exceção é usada para indicar que o template de email
 * solicitado para envio de notificação não foi encontrado
 * no sistema de templates.
 * 
 * @package MapasCulturais\Exceptions
 */
class MailTemplateNotFound extends \Exception{
}