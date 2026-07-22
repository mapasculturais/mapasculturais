<?php
namespace MapasCulturais\DoctrineMappings;

/**
 * Gerador de IDs aleatórios para o Doctrine ORM
 * 
 * Esta classe implementa um gerador de IDs que utiliza
 * a função PostgreSQL `pseudo_random_id_generator()`
 * para gerar IDs únicos e aleatórios para entidades.
 * 
 * @package MapasCulturais\DoctrineMappings
 */
class RandomIdGenerator extends \Doctrine\ORM\Id\AbstractIdGenerator {
    
    /**
     * Gera um ID aleatório para uma entidade
     * 
     * @param \Doctrine\ORM\EntityManager $em Gerenciador de entidades
     * @param object $entity Entidade
     * @return int ID gerado
     * 
     * @hook entity({hook_class_path}).randomId Permite modificar o ID gerado
     */
    public function generate(\Doctrine\ORM\EntityManager $em, $entity) {
        $table_name = $em->getClassMetadata($entity->className)->getTableName();
        $conn = $em->getConnection();
        $id = $conn->fetchScalar("SELECT pseudo_random_id_generator()");
        \MapasCulturais\App::i()->applyHookBoundTo($entity, 'entity(' . $entity->getHookClassPath() . ').randomId', ['id' => &$id]);
        return $id;
    }
    
    /**
     * Indica se este gerador é executado após a inserção
     * 
     * @return bool Sempre retorna true
     */
    public function isPostInsertGenerator() {
        true;
    }
 }