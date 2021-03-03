<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;
use Doctrine\ORM\Mapping as ORM;

/**
 * @property-read string $originSiteUrl
 * @property \MapasCulturais\Entities\Subsite $subsite
 * @property-read int $subsiteId
 */
trait EntityOriginSubsite{

    static function usesOriginSubsite(){
        return true;
    }

    function authorizedInThisSite() {
        $app = App::i();

        $current_subsite_id = $app->getCurrentSubsiteId();

        return is_null($current_subsite_id) || ($current_subsite_id === $this->_subsiteId) || $this->getOwnerUser()->id === $app->user->id;
    }

    function getOriginSiteUrl() {
        $app = App::i();
        if($this->_subsiteId && ($subsite = $app->repo("SubSite")->find($this->_subsiteId))) {
            return $subsite->url;
        } elseif(!in_array('app.subsite.mainUrl',$app->_config)) {
            return $app->_config['app.subsite.mainUrl'];
        }
    }
    
    function setSubsite(\MapasCulturais\Entities\Subsite $subsite = null){
        $this->subsite = $subsite;
        $this->_subsiteId = $subsite ? $subsite->id : null;
    }

    function getSubsiteId() {
        return $this->_subsiteId;
    }

}
