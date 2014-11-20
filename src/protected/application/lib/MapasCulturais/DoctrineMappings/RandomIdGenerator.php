<?php
namespace MapasCulturais\DoctrineMappings;

class RandomIdGenerator extends \Doctrine\ORM\Id\AbstractIdGenerator {
    public function generate(\Doctrine\ORM\EntityManager $em, $entity) {
        $table_name = $em->getClassMetadata($entity->className)->getTableName();
        $conn = $em->getConnection();
        $num = method_exists($entity, 'randomIdGeneratorInitialRange') ? $entity->randomIdGeneratorInitialRange() : 100;
        $id = $conn->fetchColumn("SELECT random_id_generator('$table_name', $num)");
        if(method_exists($entity, 'randomIdGeneratorFormat')){
            $id = $entity->randomIdGeneratorFormat($id);
        }
        return $id;
    }
    public function isPostInsertGenerator() {
        true;
    }
 }