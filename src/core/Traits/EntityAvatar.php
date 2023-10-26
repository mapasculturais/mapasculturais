<?php
namespace MapasCulturais\Traits;

/**
 * @property-read \MapasCulturais\Entities\File $avatar
 */
trait EntityAvatar{
    
    protected $_avatar;
    
    public static function usesAvatar(){
        return true;
    }

    function getAvatar(){
        if(!$this->_avatar)
            $this->_avatar = $this->getFile('avatar');

        return $this->_avatar;
    }
}
