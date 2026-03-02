<?php
namespace MapasCulturais;

/**
 * Extensão da conexão do Doctrine DBAL com métodos auxiliares para compatibilidade
 * 
 * @package MapasCulturais
 */
class Connection extends \Doctrine\DBAL\Connection {
    /**
     * Busca todos os resultados como um array associativo
     * 
     * @param string $query
     * @param array $params
     * @param array $types
     * @return array
     */
    function fetchAll (string $query, array $params = [], $types = []): array {
        return $this->fetchAllAssociative($query, $params, $types);
    }

    /**
     * Busca a primeira coluna de todos os resultados
     * 
     * @param string $query
     * @param array $params
     * @param array $types
     * @return array
     */
    function fetchColumn (string $query, array $params = [], $types = []) {
        return $this->fetchFirstColumn($query, $params, $types);
    }

    /**
     * Busca um valor escalar (primeira coluna da primeira linha)
     * 
     * @param string $query
     * @param array $params
     * @param array $types
     * @return mixed|null
     */
    function fetchScalar (string $query, array $params = [], $types = []) {
        $column = $this->fetchFirstColumn($query, $params, $types);
        return $column[0] ?? null;
    }

    /**
     * Busca uma única linha como array associativo
     * 
     * @param string $query
     * @param array $params
     * @param array $types
     * @return array|null
     */
    function fetchAssoc (string $query, array $params = [], $types = []): ?array {
        return $this->fetchAssociative($query, $params, $types) ?: null;
    }
}
