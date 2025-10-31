<?php
namespace MapasCulturais;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RuntimeException;

class Repository extends \Doctrine\ORM\EntityRepository{
    use Traits\MagicCallers;
    
    function findAllPaged($limit, $page) {
        $class = $this->getClassName();
        if($page === 0) return [];
        
        $first = ($page-1) * $limit;
        
        $dql = "SELECT e FROM {$class} e ORDER BY e.id ASC";
        $query = $this->_em->createQuery($dql)
                               ->setFirstResult($first)
                               ->setMaxResults($limit);

        $paginator = new Paginator($query);

        return  $paginator->getIterator()->getArrayCopy();
    }

    /**
     * Returns an Entity
     *
     * @param mixed $id
     * @param int|null $lockMode
     * @param int|null $lockVersion
     * 
     * @return Entity
     */
    function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * 
     * @param array<string, mixed> $criteria 
     * @param array<string, string>|null $orderBy 
     * @param int|null $limit 
     * @param int|null $offset 
     * @return Entity[] 
     * 
     * @throws RuntimeException 
     */
    function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        foreach ($criteria as $key => &$value) {
            if(is_array($value) && empty($value)) {
                $value = [-1];
            }
        }

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
}