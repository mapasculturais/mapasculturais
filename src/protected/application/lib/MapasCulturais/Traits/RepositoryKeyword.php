<?php
namespace MapasCulturais\Traits;

use \MapasCulturais\App;

trait RepositoryKeyword{
    function usesKeyword(){
        return true;
    }
    
    protected function _getKeywordDQLFrom(){
        $class = $this->getClassName();
        
        $join = '';
        
        App::i()->applyHookBoundTo($this, 'repo(' . $class::getHookClassPath() . ').getIdsByKeywordDQL.join', array(&$join));
        
        return "$class e $join";
    }
    
    protected function _getKeywordDQLWhere(){
        $class = $this->getClassName();
        
        $where = '';
        
        App::i()->applyHookBoundTo($this, 'repo(' . $class::getHookClassPath() . ').getIdsByKeywordDQL.where', array(&$where));
        
        return "lower(e.name) LIKE lower(:keyword) $where";
    }
    
    function getIdsByKeyword($keyword){
        $keyword = "%{$keyword}%";
        
        $from = $this->_getKeywordDQLFrom();
        $where = $this->_getKeywordDQLWhere();
        
        $dql = "SELECT DISTINCT e.id FROM $from WHERE $where";
        
        $query = $this->_em->createQuery($dql);
        
        $query->setParameter('keyword', $keyword);
        
        $ids = array_map(function($e) {
            return $e['id'];
        }, $query->getArrayResult());

        return $ids;
    }
    
    function findByKeyword(array $keyword, $orderBy = null, $limit = null, $offset = null) {
        $keyword = "%{$keyword}%";
        
        $from = $this->_getKeywordDQLFrom();
        $where = $this->_getKeywordDQLWhere();
        
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