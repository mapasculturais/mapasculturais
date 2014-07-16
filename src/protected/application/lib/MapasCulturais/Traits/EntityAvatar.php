<?php
namespace MapasCulturais\Traits;

trait EntityAvatar{
    
    protected $_avatar;
    
    function usesAvatar(){
        return true;
    }

    function getAvatar(){
        if(!$this->_avatar)
            $this->_avatar = $this->getFile('avatar');

        return $this->_avatar;
    }
}
