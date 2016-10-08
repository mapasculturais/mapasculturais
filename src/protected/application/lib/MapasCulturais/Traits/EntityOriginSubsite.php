<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;
use Doctrine\ORM\Mapping as ORM;

trait EntityOriginSubsite{
    /**
     * @var \MapasCulturais\Entities\Subsite
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Subsite", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="subsite_id", referencedColumnName="id", nullable=true)
     */
    protected $subsite;

    /**
     * @var integer
     *
     * @ORM\Column(name="subsite_id", type="integer", nullable=true)
     */
    protected $_subsiteId;
    
    static function usesOriginSubsite(){
        return true;
    }
    
    function authorizedInThisSite() {
        $app = App::i();
        
        $current_subsite_id = $app->getCurrentSubsiteId();
        
        return is_null($current_subsite_id) || ($current_subsite_id === $this->_subsiteId);
    }
    
    /** @ORM\PrePersist */
    public function __saveCurrentSubsiteId($args = null){ 
        $app = App::i();
        
        $this->_subsiteId = $app->getCurrentSubsiteId();
    }

}