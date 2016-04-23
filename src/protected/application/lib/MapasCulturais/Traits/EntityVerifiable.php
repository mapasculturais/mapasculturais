<?php
namespace MapasCulturais\Traits;

/**
 * Defines that an entity can be verified by users with the *staff* role.
 * 
 * Use this trait only in subclasses of **\MapasCulturais\Entity** with **isVerified** property.
 */
trait EntityVerifiable{
    /**
     * This entity is verifiable
     * @return bool true
     */
    public static function usesVerifiable(){
        return true;
    }

    /**
     * Verfify the entity
     */
    function verify(){
        $this->setIsVerified(true);
    }

    /**
     * Removes the entity verification
     */
    function cancelVerification(){
        $this->setIsVerified(false);
    }

    /**
     * Sets the **isVerified** property of this entity
     * 
     * @param bool $val
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied
     */
    public function setIsVerified($val){
        $val = (bool) $val;
        if($val)
            $this->checkPermission('verify');
        else
            $this->checkPermission('memoveVerification');

        $this->isVerified = $val;
    }

    /**
     * Checks if user can verify this entity
     * 
     * @param \MapasCulturais\Entities\User $user
     * @return boolean
     */
    protected function canUserVerify($user){
        if($user->is('guest'))
            return false;

        return $user->is('admin') || $this->canUser('modify') && $user->is('staff');
    }

    /**
     * Checks if user can remove the verification of this entity
     * 
     * @param \MapasCulturais\Entities\User $user
     * @return boolean
     */
    protected function canUserRemoveVerification($user = null){
        return $this->canUserVerify($user);
    }
}