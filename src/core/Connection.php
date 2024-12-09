<?php
namespace MapasCulturais;

class Connection extends \Doctrine\DBAL\Connection {
    function fetchAll (string $query, array $params = [], $types = []): array {
        return $this->fetchAllAssociative($query, $params, $types);
    }

    function fetchColumn (string $query, array $params = [], $types = []) {
        return $this->fetchFirstColumn($query, $params, $types);
    }

    function fetchScalar (string $query, array $params = [], $types = []) {
        $column = $this->fetchFirstColumn($query, $params, $types);
        return $column[0] ?? null;
    }

    function fetchAssoc (string $query, array $params = [], $types = []): ?array {
        return $this->fetchAssociative($query, $params, $types) ?: null;
    }
}