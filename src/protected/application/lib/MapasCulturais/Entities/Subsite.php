<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Subsite
 * @property \MapasCulturais\Entities\Agent $owner The owner of this subsite
 *
 * @ORM\Table(name="subsite", indexes={
 *  @ORM\Index(name="url_index", columns={"url"}),
 *  @ORM\Index(name="alias_url_index", columns={"alias_url"})
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Subsite")
 * @ORM\HasLifecycleCallbacks
 */
class Subsite extends \MapasCulturais\Entity
{
    use Traits\EntityOwnerAgent,
        Traits\EntityFiles,
        Traits\EntityMetadata,
        Traits\EntityMetaLists,
        Traits\EntityGeoLocation,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityArchive;
        
    protected $__enableMagicGetterHook = true;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="subsite_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="agent_id", type="integer", nullable=false)
     */
    protected $_ownerId;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="alias_url", type="string", length=255, nullable=true)
     */
    protected $aliasUrl;

    /**
     * @var \MapasCulturais\Entities\Role[] Role
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Role", mappedBy="subsite", cascade="remove", fetch="EAGER", orphanRemoval=true)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id", referencedColumnName="subsite_id", onDelete="CASCADE")
     * })
    */
    protected $_roles;

    /**
     * @var string
     *
     * @ORM\Column(name="verified_seals", type="json_array", nullable=true)
     */
    protected $verifiedSeals = [];

    function setVerifiedSeals($val) {
        if(is_string($val)) {
            if(trim($val)){
                $val = explode(';', $val);
            } else {
                $val = [];
            }
        } else if( $val ){
            $val = (array) $val;
        } else {
            $val = [];
        }

        $val = array_map(function($v) { return (int) $v; }, $val);

        $this->verifiedSeals = $val;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="namespace", type="string", length=50, nullable=false)
     */
    protected $namespace = 'Subsite';

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SubsiteMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
     */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\SubsiteFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SubsiteFile", fetch="EAGER", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__files;


    protected $filters = [];

    static function getValidations(){
        return [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome da instalação é obrigatório')
            ],
            'url' => [
                'required' => \MapasCulturais\i::__('A url da instalação é obrigatória'),
                'unique' => \MapasCulturais\i::__('Esta URL já está sendo utilizada')
            ],
            'aliasUrl' => [
                'unique' => \MapasCulturais\i::__('Esta URL já está sendo utilizada')
            ]
        ];
    }


    public function __construct() {
        $this->owner = App::i()->user->profile;
        parent::__construct();
    }

    public function getEditUrl() {
        return $this->getSingleUrl();
    }

    public function getSubsiteUrl() {
        $app = \MapasCulturais\App::i();
        $req = $app->request;

        return $req->getScheme() . "://" . $this->url;
    }

    protected $_logo;

    function getLogo(){
        if(!$this->_logo)
            $this->_logo = $this->getFile('logo');

        return $this->_logo;
    }

    protected $_share;

    function getShareImage(){
        if(!$this->_share)
            $this->_share = $this->getFile('share');

        return $this->_share;
    }

    protected $_background;

    function getBackground(){

        if(!$this->_background)
            $this->_background = $this->getFile('background');

        return $this->_background;
    }

    protected $_institute;

    function getInstitute(){
        if(!$this->_institute)
            $this->_institute = $this->getFile('institute');

        return $this->_institute;
    }

    protected $_favicon;

    function getFavicon(){
        if(!$this->_favicon)
            $this->_favicon = $this->getFile('favicon');

        return $this->_favicon;
    }

    function getParentIds() {
        $app = App::i();

        $cid = "subsite-parent-ids:{$this->id}";

        if ($app->cache->contains($cid)) {
            $ids = $app->cache->fetch($cid);
        } else {
            // @TODO: quando o parent estiver implementado fazer percorrer a arvore....
            $ids = [$this->id];

            $app->cache->save($cid, $ids, 300);
        }

        return $ids;
    }


    public function applyApiFilters(){
        $app = App::i();

        $subsite_meta = $app->getRegisteredMetadata("MapasCulturais\Entities\Subsite");


        $IN = ['type'];

        foreach($subsite_meta as $k => $v) {
            $meta_name = $k;

            $pos_meta_filter      = strpos($meta_name,"filtro_");
            $pos_meta_controller  = 0;
            $controller           = "";
            $pos_meta_type        = 0;
            $meta_type            = "";

            if($pos_meta_filter === 0) {
                $meta_name = substr($meta_name,strpos($meta_name,"_")+1);
                $pos_meta_controller = strpos($meta_name,"_");
                if($pos_meta_controller > 0) {
                    $controller = substr($meta_name,0,$pos_meta_controller);
                    $meta_name = substr($meta_name,$pos_meta_controller+1);
                    $pos_meta_type = strpos($meta_name,"_");
                    if($pos_meta_type > 0) {
                        $meta_type = substr($meta_name,0,$pos_meta_type);
                        $meta_name = substr($meta_name,$pos_meta_type+1);

                        if($this->$k) {
                            $meta_name = $meta_type == "term"? "term:".$meta_name: $meta_name;
                            $meta_cont = $this->$k;
                            $meta_cont = is_array($meta_cont)? implode(',',$meta_cont): $meta_cont;
                            $this->filters[$controller] = isset($this->filters[$controller]) ? $this->filters[$controller] : [];
                            if(in_array($meta_name, $IN)){
                                $this->filters[$controller][$meta_name] = "IN(" . str_replace(";",",",$meta_cont) . ")";

                            } else {
                                $this->filters[$controller][$meta_name] = "IIN(" . str_replace(";",",",$meta_cont) . ")";
                            }
                        }
                    }
                }
            }
        }

        $subsite_id = $app->getCurrentSubsiteId();

        $app->applyHookBoundTo($this, 'subsite.applyFilters:before');

        foreach($this->filters as $controller_id => $entity_filters){
            $entity_class_name = $app->controller($controller_id)->entityClassName;
            $query = new \MapasCulturais\ApiQuery($entity_class_name, $entity_filters, $this->id);

            $this->_entityApiQueryFilters[$entity_class_name] = $query;

            $app->hook("API.<<*>>({$controller_id}).query", function(&$qdata, &$select_properties, &$dql_joins, &$dql_where) use($query) {
                $query_dql = $query->getSubDQL();
                $dql_where .=  " AND e.id IN({$query_dql})";
            });
        }

        $app->hook("API.<<*>>(PROJECT).query", function(&$qdata, &$select_properties, &$dql_joins, &$dql_where) use($subsite_id) {
            $dql_where .= " AND e._subsiteId = {$subsite_id}";
        });

        $app->applyHookBoundTo($this, 'subsite.applyFilters:after');
    }

    protected $_entityApiQueryFilters = [];

    public function getApiQueryFilter($entity_class){
        return isset($this->_entityApiQueryFilters[$entity_class]) ? $this->_entityApiQueryFilters[$entity_class] : null;
    }

    function jsonSerialize() {
        $result = [
            'id' => $this->id,
            'createTimestamp' => $this->createTimestamp,
            'status' => $this->status,
            'url' => $this->url,
            'aliasUrl' => $this->aliasUrl,
            'verifiedSeals' => $this->verifiedSeals,
            'controllerId' => "subsite",
            "deleteUrl" => $this->deleteUrl,
            "editUrl" => $this->editUrl,
            "singleUrl" => $this->singleUrl,

        ];

        return $result;
    }


    public function applyConfigurations(&$config){
        $app = App::i();

        $app->applyHookBoundTo($this, 'subsite.applyConfigurations:before', ['config' => &$config]);

        $config['app.verifiedSealsIds'] = $this->verifiedSeals;


        if($this->longitude && $this->longitude) {
            $config['maps.center'] = array($this->latitude, $this->longitude);
        }

        if($this->zoom_default) {
            $config['maps.zoom.default'] = $this->zoom_default;
        }

        if($this->zoom_max){
            $config['maps.zoom.max'] = $this->zoom_max;
        }

        if($this->zoom_min){
            $config['maps.zoom.min'] = $this->zoom_min;
        }

        $domain = $this->url;

        foreach($app->plugins as $plugin){
            if(get_class($plugin) == 'SubsiteDomainSufix\Plugin'){
                $sufix = $plugin->getSufix();
                if($sufix[0] == '.'){
                    $domain = str_replace($sufix, '', $domain);
                } else {
                    $domain = str_replace('.' . $sufix, '', $domain);
                }
                break;
            }
        }

        $assets_folder = "assets/{$domain}/";

        $config['base.assetUrl'] = $app->baseUrl . $assets_folder;
        $config['themes.assetManager']->config['publishPath'] = BASE_PATH . $assets_folder;

        // @TODO: passar esta parte abaixo para o tema

        $entidades = explode(';', $this->entidades_habilitadas);

        if(!in_array('Agents', $entidades)){
            $config['app.enabled.agents'] = false;
        }

        if (!in_array('Projects', $entidades)) {
            $config['app.enabled.projects'] = false;
        }

        if (!in_array('Spaces', $entidades)) {
            $config['app.enabled.spaces'] = false;
        }

        if (!in_array('Events', $entidades)) {
            $config['app.enabled.events'] = false;
        }

        if (!in_array('Opportunities', $entidades)) {
            $config['app.enabled.opportunities'] = false;
        }





        $app->applyHookBoundTo($this, 'subsite.applyConfigurations:after', ['config' => &$config]);

    }


    public function getSassCacheId(){
        return "Subsite-{$this->id}:_variables.scss";
    }

    protected function canUserDestroy($user) {
        return $user->is('saasSuperAdmin');
    }

    protected function canUserRemove($user) {
        return $user->is('saasAdmin');
    }

    protected function canUserModify($user) {
        return $user->is('superAdmin', $this->id);
    }

    function clearCache(){
        $this->checkPermission('modify');

        $app = App::i();

        $app->msCache->delete($this->getSassCacheId());

        $subsite_cache = clone $app->cache;
        $subsite_cache->deleteAll();

    }

    public function save($flush = false) {
        parent::save($flush);
        $this->clearCache();
    }

    /** @ORM\PreRemove */
    public function _setNullSubsiteId() {
        $app = App::i();
        $subsite_id = $this->id;
        $query = "UPDATE \MapasCulturais\Entities\Agent a SET a.subsite = NULL WHERE a._subsiteId = {$subsite_id}";
        $q = $app->em->createQuery($query);
        $q->execute();

        $query = "UPDATE \MapasCulturais\Entities\Space s SET s.subsite = NULL WHERE s._subsiteId = {$subsite_id}";
        $q = $app->em->createQuery($query);
        $q->execute();

        $query = "UPDATE \MapasCulturais\Entities\Event e SET e.subsite = NULL WHERE e._subsiteId = {$subsite_id}";
        $q = $app->em->createQuery($query);
        $q->execute();

        $query = "UPDATE \MapasCulturais\Entities\Project p SET p.subsite = NULL WHERE p._subsiteId = {$subsite_id}";
        $q = $app->em->createQuery($query);
        $q->execute();

        $query = "UPDATE \MapasCulturais\Entities\Seal s SET s.subsite = NULL WHERE s._subsiteId = {$subsite_id}";
        $q = $app->em->createQuery($query);
        $q->execute();

        $query = "UPDATE \MapasCulturais\Entities\Registration r SET r.subsite = NULL WHERE r._subsiteId = {$subsite_id}";
        $q = $app->em->createQuery($query);
        $q->execute();

        $query = "UPDATE \MapasCulturais\Entities\UserApp u SET u.subsite = NULL WHERE u._subsiteId = {$subsite_id}";
        $q = $app->em->createQuery($query);
        $q->execute();

        $app->em->flush();
    }

    
    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args = null){ parent::prePersist($args); }
    /** @ORM\PostPersist */
    public function postPersist($args = null){ parent::postPersist($args); }

    /** @ORM\PreRemove */
    public function preRemove($args = null){ parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args = null){ parent::postRemove($args); }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null){ parent::preUpdate($args); }
    /** @ORM\PostUpdate */
    public function postUpdate($args = null){ parent::postUpdate($args); }
}
