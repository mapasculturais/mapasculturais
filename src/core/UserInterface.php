<?php
namespace MapasCulturais;

/**
 * Interface para usuários do MapasCulturais
 * 
 * Define os métodos básicos que todas as implementações de usuário devem fornecer,
 * incluindo autenticação e verificação de permissões.
 * 
 * @package MapasCulturais
 */
interface UserInterface{
    /**
     * Verifica se o usuário possui um determinado papel (role)
     * 
     * @param string $role Nome do papel a ser verificado
     * @param mixed $subsite_id ID do subsite (opcional)
     * @return bool
     */
    function is(string $role, $subsite_id = false);

    /**
     * Verifica se o usuário é procurador para uma determinada ação
     * 
     * @param string $action Ação para a qual verificar a procuração
     * @param mixed $user Usuário relacionado (opcional)
     * @return bool
     */
    function isAttorney($action, $user= null);
}
