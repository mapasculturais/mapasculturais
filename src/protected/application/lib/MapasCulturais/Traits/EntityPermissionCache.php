<?php
namespace MapasCulturais\Traits;

trait EntityPermissionCache{
    
    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\PermissionCache", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $__permissionsCache;
    
    public static function usesPermissionCache(){
        return true;
    }

}
