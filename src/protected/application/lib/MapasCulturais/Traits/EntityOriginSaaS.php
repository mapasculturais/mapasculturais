<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;
use Doctrine\ORM\Mapping as ORM;

trait EntityOriginSaaS{
    /**
     * @var \MapasCulturais\Entities\SaaS
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\SaaS", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="saas_id", referencedColumnName="id", nullable=true)
     */
    protected $saas;

    /**
     * @var integer
     *
     * @ORM\Column(name="saas_id", type="integer", nullable=true)
     */
    protected $_saasId;
    
    function authorizedInThisSite() {
        $app = App::i();
        
        $current_saas_id = $app->getCurrentSaaSId();
        
        return is_null($current_saas_id) || ($current_saas_id === $this->_saasId);
    }
    
    /** @ORM\PrePersist */
    public function __saveCurrentSaaSId($args = null){ 
        $app = App::i();
        
        $this->_saasId = $app->getCurrentSaaSId();
    }

}