<?php
namespace MapasCulturais;

use MapasCulturais\Traits;

/**
 * Classe base para Plugins
 * 
 * Esta classe abstrata estende a classe Module e fornece a estrutura básica
 * para plugins no sistema Mapas Culturais. Plugins são módulos que adicionam
 * funcionalidades específicas ao sistema.
 * 
 * @package MapasCulturais
 */
abstract class Plugin extends Module {
    
    /**
     * Método executado antes da inicialização do plugin
     * 
     * Este método pode ser sobrescrito por plugins para executar
     * ações antes da inicialização completa do sistema.
     * 
     * @return void
     */
    static function preInit() {}
    
    /**
     * Verifica se o módulo é um plugin
     * 
     * @return bool Sempre retorna true para plugins
     */
    static function isPlugin() {
        return true;
    }
}