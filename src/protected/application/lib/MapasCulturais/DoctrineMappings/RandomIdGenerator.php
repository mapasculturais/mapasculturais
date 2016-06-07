<?php
namespace MapasCulturais\DoctrineMappings;

class RandomIdGenerator extends \Doctrine\ORM\Id\AbstractIdGenerator {
    public function generate(\Doctrine\ORM\EntityManager $em, $entity) {
        $table_name = $em->getClassMetadata($entity->className)->getTableName();
        $conn = $em->getConnection();
        $id = $conn->fetchColumn("SELECT pseudo_random_id_generator()");
        return $id;
    }
    public function isPostInsertGenerator() {
        true;
    }
 }