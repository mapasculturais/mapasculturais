<?php
namespace MapasCulturais\Traits;

use \MapasCulturais\App;

/**
 * Implements methods to find entities by a keyword.
 * 
 * Use this trait only in subclasses of **\MapasCulturais\Repository**.
 * 
 * @hook repo({ENTITY}).getIdsByKeywordDQL.join
 * @hook repo({ENTITY}).getIdsByKeywordDQL.where
 */
trait RepositoryKeyword{
    
    /**
     * This repository uses Keyword
     * @return true
     */
    public static function usesKeyword(){
        return true;
    }

    /**
     * Returns the **FROM** part of DQL used to find the entities by keyword
     * 
     * @param string $keyword
     * @return string
     * 
     * @hook **repo({ENTITY}).getIdsByKeywordDQL.join**
     */
    protected function _getKeywordDQLFrom($keyword, $alias = 'keyword'){
        $class = $this->getClassName();

        $join = '';

        App::i()->applyHookBoundTo($this, 'repo(' . $class::getHookClassPath() . ').getIdsByKeywordDQL.join', [&$join, $keyword, $alias]);

        return "$class e $join";
    }

    

    /**
     * Returns the **WHERE** part of DQL used to find the entities by keyword
     * 
     * @param string $keyword
     * @return string
     * 
     * @hook **repo({ENTITY}).getIdsByKeywordDQL.where**
     */
    protected function _getKeywordDQLWhere($keyword, $alias = 'keyword'){
        $class = $this->getClassName();

        $app = App::i();

        $fields = $app->em->getClassMetadata($class)->fieldMappings;

        if(isset($fields['name'])) {
            $where = " unaccent(lower(e.name)) LIKE unaccent(lower(:{$alias}))";
        } else {
            $where = '';
        }

        App::i()->applyHookBoundTo($this, 'repo(' . $class::getHookClassPath() . ').getIdsByKeywordDQL.where', [&$where, $keyword, $alias]);

        return  $where;
    }

    function getKeywordDQLWhere($keyword, $alias = 'keyword') {
        return $this->_getKeywordDQLWhere($keyword, $alias);
    }
    
    function getIdsByKeywordDQL($keyword, $alias = 'keyword'){
        $keyword = "%{$keyword}%";

        $from = $this->_getKeywordDQLFrom($keyword, $alias);
        $where = $this->_getKeywordDQLWhere($keyword, $alias);

        $dql = "SELECT e.id FROM $from WHERE $where";

        return $dql;
    }

    /**
     * Returns the found entities ids
     * 
     * @param string $keyword
     * @return array
     */
    function getIdsByKeyword($keyword){
        $keywords = explode(';', $keyword);
        $result = [];
        eval(\psy\sh());
        foreach($keywords as $keyword) {
            $dql = $this->getIdsByKeywordDQL($keyword);
            $query = $this->_em->createQuery($dql);
    
            $query->setParameter('keyword', $keyword);
    
            $ids = array_map(function($e) {
                return $e['id'];
            }, $query->getArrayResult());

            $result = [...$result, ...$ids];
        }

        return array_values(array_unique($result));
    }

    /**
     * Returns the found entities
     * 
     * @param string $keyword
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @return \MapasCulturais\Entity[]
     */
    function findByKeyword($keyword, $orderBy = null, $limit = null, $offset = null) {
        $keyword = "%{$keyword}%";

        $from = $this->_getKeywordDQLFrom($keyword);
        $where = $this->_getKeywordDQLWhere($keyword);

        $ob = '';
        if(is_array($orderBy)){
            $ob = 'ORDER BY ';
            $first = true;
            foreach($orderBy as $prop => $order){
                if(!$first) $ob .= ', ';
                $ob .= "{$prop} {$order}";
            }
        }

        $dql = "SELECT e FROM $from WHERE $where $ob";

        $query = App::i()->em->createQuery($dql);

        if($limit) $query->setMaxResults($limit);
        if($offset) $query->setFirstResult($offset);


        $query->setParameter('keyword', $keyword);

        return $query->getResult();
    }
}