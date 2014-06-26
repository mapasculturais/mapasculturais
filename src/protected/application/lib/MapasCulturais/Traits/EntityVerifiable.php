<?php
namespace MapasCulturais\Traits;

trait EntityVerifiable{
    public function usesVerifiable(){
        return true;
    }

    function verify(){
        $this->setIsVerified(true);
    }

    function cancelVerification(){
        $this->setIsVerified(false);
    }

    public function setIsVerified($val){
        $val = (bool) $val;
        if($val)
            $this->checkPermission('verify');
        else
            $this->checkPermission('memoveVerification');

        $this->isVerified = $val;
    }

    protected function canUserVerify($user){
        if($user->is('guest'))
            return false;

        return $user->is('admin') || $this->canUser('modify') && $user->is('staff');
    }

    protected function canUserRemoveVerification($user = null){
        return $this->canUserVerify($user);
    }
}