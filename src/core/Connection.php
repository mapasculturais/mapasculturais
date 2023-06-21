<?php
namespace MapasCulturais;

class Connection extends \Doctrine\DBAL\Connection {
    function fetchAll (string $query, array $params = [], $types = []): array {
        return $this->fetchAllAssociative($query, $params, $types);
    }
}