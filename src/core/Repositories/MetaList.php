<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\App;

/**
 * Repositório para listas de metadados
 * 
 * Este repositório fornece métodos específicos para consulta
 * e manipulação de listas de metadados no sistema,
 * com foco em operações relacionadas a grupos de metadados.
 * 
 * @package MapasCulturais\Repositories
 */
class MetaList extends \MapasCulturais\Repository{
    
    /**
     * Encontra listas de metadados por proprietário e grupo
     * 
     * @param \MapasCulturais\Entity $owner Proprietário da lista
     * @param string $group Grupo da lista
     * @return array Listas de metadados encontradas
     */
    function findByGroup(\MapasCulturais\Entity $owner, $group){
        $result = $this->findBy([
            'objectType' => $owner->getClassName(), 
            'objectId' => $owner->id, 
            'group' => $group
        ], ['id'=>'ASC']);
        
        return $result;
    }

    /**
     * Encontra uma lista de metadados por proprietário e grupo
     * 
     * @param \MapasCulturais\Entity $owner Proprietário da lista
     * @param string $group Grupo da lista
     * @return \MapasCulturais\Entities\MetaList|null Lista de metadados encontrada
     */
    function findOneByGroup(\MapasCulturais\Entity $owner, $group){
        $result = $this->findOneBy([
            'objectType' => $owner->getClassName(), 
            'objectId' => $owner->id, 
            'group' => $group
        ]);

        return $result;
    }

    /**
     * Encontra listas de metadados por proprietário agrupadas por grupo
     * 
     * @param \MapasCulturais\Entity $owner Proprietário das listas
     * @return array Listas de metadados agrupadas por grupo
     */
    function findByOwnerGroupedByGroup(\MapasCulturais\Entity $owner){

        $metalists = $this->findBy([
            'objectId' => $owner->id, 
            'objectType' =>  $owner->getClassName()
        ]);

        $result = [];

        if($metalists){
            foreach($metalists as $metalist){
                if(!key_exists($metalist->group, $result))
                    $result[trim($metalist->group)] = [];

                $result[trim($metalist->group)][] = $metalist;
            }
        }

        ksort($result);

        return $result;
    }
}