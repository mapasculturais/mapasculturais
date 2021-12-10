<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;
use \MapasCulturais\i;

/**
 * File
 *
 * @property-read int $id File Id
 * @property-read string $md5 File MD5
 * @property-read string $mimeType File Mime Type
 * @property-read string $name File name
 * @property-read string $group File Group (gallery|avatar|download|etc.)
 * @property-read \MapasCulturais\Entity $owner File Owner
 * @property-read \DateTime $createTimestamp File Create Timestamp
 * @property-read \MapasCulturais\Entity $owner The Owner of this File
 * 
 * @property bool $private Is this file private?
 *
 * @property-read array $tmpFile $_FILE
 * 
 * @ORM\Table(name="file",indexes={
 *      @ORM\Index(name="file_owner_index", columns={"object_type", "object_id"}),
 *      @ORM\Index(name="file_group_index", columns={"grp"}),
 * })
 * 
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\File")
 * @ORM\HasLifecycleCallbacks
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="object_type")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Opportunity"                   = "\MapasCulturais\Entities\OpportunityFile",
        "MapasCulturais\Entities\Project"                       = "\MapasCulturais\Entities\ProjectFile",
        "MapasCulturais\Entities\Event"                         = "\MapasCulturais\Entities\EventFile",
        "MapasCulturais\Entities\Agent"                         = "\MapasCulturais\Entities\AgentFile",
        "MapasCulturais\Entities\Space"                         = "\MapasCulturais\Entities\SpaceFile",
        "MapasCulturais\Entities\Seal"                          = "\MapasCulturais\Entities\SealFile",
        "MapasCulturais\Entities\Registration"                  = "\MapasCulturais\Entities\RegistrationFile",
        "MapasCulturais\Entities\RegistrationFileConfiguration" = "\MapasCulturais\Entities\RegistrationFileConfigurationFile",
        "MapasCulturais\Entities\Subsite"                       = "\MapasCulturais\Entities\SubsiteFile"
   })
 */
abstract class File extends \MapasCulturais\Entity
{
    use \MapasCulturais\Traits\EntityFiles;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="file_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="md5", type="string", length=32, nullable=false)
     */
    protected $md5;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", type="string", length=32, nullable=false)
     */
    protected $mimeType;

    /**
     * @var string
     *
     * @ORM\Column(name="grp", type="string", length=32, nullable=false)
     */
    protected $group;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024, nullable=true)
     */
    protected $_path;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="private", type="boolean", nullable=false)
     */
    protected $private = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * An array like an item of $_FILE
     * @example array(
     *              name => filename.jpg
     *              type => mime/type
     *              tmp_name => /tmp/tmpfile
     *              error => 0
     *              size => 1234567
     *          )
     * @var array
     */
    protected $tmpFile = ['error' => '', 'name' => '', 'type' => '', 'tmp_name' => '', 'size' => 0];

    /**
     * Creates a new file from upload
     * @example array(
     *              name => filename.jpg
     *              type => mime/type
     *              tmp_name => /tmp/tmpfile
     *              error => 0
     *              size => 1234567
     *          )
     * @param array $file
     */
    public function __construct(array $tmp_file) {
        $this->tmpFile = $tmp_file;
        $this->md5 = md5_file($tmp_file['tmp_name']);
        $this->name = $tmp_file['name'];
        $this->mimeType = $tmp_file['type'];

        if(isset($tmp_file['parent'])){
            $this->parent = $tmp_file['parent'];
        }

        parent::__construct();
    }

    static function getValidations() {
        return [
            'mimeType' => [
                'v::not(v::regex("#.php$#"))' => i::__('Tipo de arquivo não permitido')
            ]
        ];
    }

    /**
     * Returns the controller with the same name in the parent namespace if it exists.
     *
     * @return \MapasCulturais\Controller The controller
     */
    public function getController(){
        return App::i()->getControllerByEntity(__CLASS__);
    }

    /**
     * Returns the controller with the same name in the parent namespace if it exists.
     *
     * @return \MapasCulturais\Controller The controller
     */
    public function getControllerId(){
        return App::i()->getControllerIdByEntity(__CLASS__);
    }

    protected function canUserCreate($user){
        if($this->_parent){
            return true;
        }else{
            return $this->owner->canUser('modify');
        }
    }

    protected function canUserRemove($user){
        return $this->owner->canUser('modify');
    }

    protected function canUserModify($user){
        return $this->owner->canUser('modify');
    }

    protected function canUserView($user){
        if($owner = $this->owner){
            return $owner->canUser('view');
        }else{
            return false;
        }
    }

    protected function canUserChangePrivacy($user){
        return $this->canUser('modify');
    }

    public function save($flush = false) {
        if(preg_match('#.php$#', $this->mimeType))
            throw new \MapasCulturais\Exceptions\PermissionDenied($this->ownerUser, $this, 'save');

        $app = App::i();
        
        $file_group = $app->getRegisteredFileGroup($this->owner->controllerId, $this->getGroup());

        if(is_null($this->private)){
            if ( is_object($file_group) && $file_group instanceof \MapasCulturais\Definitions\FileGroup && $file_group->private === true){
                $this->private = true;
            } else {
                if(!isset($this->owner->status) || $this->owner->status > 0){
                    $this->private = false;
                } else {
                    $this->private = true;
                }
            }
        }
        
        parent::save($flush);
    }

    public function makePrivate(){
        if($this->private){
            return;
        }

        $this->togglePrivacy();

        foreach($this->getChildren() as $file){
            $file->makePrivate();
        }
    }

    public function makePublic(){
        if(!$this->private){
            return;
        }

        $this->togglePrivacy();

        foreach($this->getChildren() as $file){
            $file->makePublic();
        }
    }

    protected function togglePrivacy(){
        $this->checkPermission('changePrivacy');

        $app = App::i();

        $app->storage->togglePrivacy($this);

        $this->private = ! $this->private;

        $this->save(true);

        $cache_id = "{$this}:url";

        $app->cache->delete($cache_id);
    }

    static function sortFilesByGroup($files){
        $app = App::i();
        $result = [];

        if($files){
            foreach($files as $file){
                $registeredGroup = $app->getRegisteredFileGroup($file->owner->controllerId, $file->group);

                if($registeredGroup && $registeredGroup->unique || $file->group === 'zipArchive' || strpos($file->group, 'rfc_') === 0){
                    $result[trim($file->group)] = $file;
                }else{
                    if(!key_exists($file->group, $result))
                        $result[trim($file->group)] = [];

                    $result[trim($file->group)][] = $file;
                }
            }
        }

        ksort($result);

        return $result;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'md5' => $this->md5,
            'mimeType' => $this->mimeType,
            'name' => $this->name,
            'description' => $this->description,
            'group' => $this->group,
            'files' => $this->getFiles(),
            'url' => $this->url,
            'deleteUrl' => $this->deleteUrl,
        ];
    }


    public function getGroup(){
        return trim($this->group);
    }

    public function setGroup($val){
        $this->group = trim($val);
    }

    /**
     * Returns the url to this file
     * @return string the url to this file
     */
    public function getUrl(){
    
        $app = App::i();
        $cache_id = "{$this}:url";
        
        if($app->config['app.useFileUrlCache'] && $app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $url = $app->storage->getUrl($this);

        if($app->config['app.useFileUrlCache']){
            $app->cache->save($cache_id, $url, $app->config['app.fileUrlCache.lifetime']);
        }

        return $url;
    }


    public function getChildren(){
        $result = [];
        $app = App::i();
        if(isset($this->tmpFile['name']) && $this->tmpFile['name']){
            $app->em->refresh($this->owner);
        }

        foreach($this->owner->files as $group => $files){
            if(substr($group, 0, 4) === 'img:'){
                foreach($files as $file){
                    if($file->parent->equals($this)){
                        $result[] = $file;
                    }
                }
            }
        }

        return $result;
    }

    public function getPath(){
        return App::i()->storage->getPath($this);
    }
    
    public function getRelativePath($get_from_storage = true){
        if(empty($this->_path) && $get_from_storage){
            $this->_path = App::i()->storage->getPath($this, true);
        }
        
        return $this->_path;
    }

    public function transform($transformation_name){
        $result = null;
        if(preg_match('#^image/#i',$this->mimeType)){
            $app = App::i();

            $wideimage_operations = $app->getRegisteredImageTransformation($transformation_name);

            $app->disableAccessControl();

            if(preg_match('#^cropCenter[ ]*\([ ]*(\d+)[ ]*,[ ]*(\d+)[ ]*\)$#', $wideimage_operations, $match)){
                $transformed = $this->_cropCenter($transformation_name, $match[1], $match[2]);
            }else{
                $transformed = $this->_transform($transformation_name, $wideimage_operations);
            }

            $app->enableAccessControl();
            $result = $transformed;
        }

        return $result;
    }

    /**
     * Returns a transformed image.
     *
     * @param string $wideimage_operations
     *
     * @example $image->transform('resize(200, 100)->crop(50, 50, 30, 20)->rotate(20)')
     *
     * @return File
     */
    protected function _transform($transformation_name, $wideimage_operations){
        if(!trim($wideimage_operations))
            return $this;


        $transformation_group_name = 'img:' . $transformation_name;

        $owner = $this->owner;
        

        $wideimage_operations = strtolower(str_replace(' ', '', $wideimage_operations));

        $hash = md5($this->md5 . $this->name . $wideimage_operations);

        // modify the filename adding the hash before the file extension. ex: file.png => file-5762a89ee8e05021006d6c35095903b5.png
        $image_name = preg_replace("#(\.[[:alnum:]]+)$#i", '-' . $hash . '$1', $this->name);

        if(isset($owner->files[$transformation_group_name]) && is_array($owner->files[$transformation_group_name])){
            foreach($owner->files[$transformation_group_name] as $transformed){
                if($transformed->parent->equals($this) && $transformed->group == $transformation_group_name){
                    return $transformed;
                }
            }
        }

        if($transformed = $this->repo()->findOneBy(['parent' => $this, 'group' => $transformation_group_name])){
            return $transformed;
        }

        $path = $this->getPath();
        if(!file_exists($path)
            || !is_writable($path)
            || !is_writable(dirname($path))
            || filesize($path) == 0) {
            return $this;
        }

        $new_image = \WideImage\WideImage::load($path);

        eval('$new_image = $new_image->' . $wideimage_operations . ';');

        $tmp_filename = sys_get_temp_dir() . '/' . $image_name;

        $new_image->saveToFile( $tmp_filename );

        $file_class = $this->getClassName();

        $image = new $file_class([
            'error' => UPLOAD_ERR_OK,
            'name' => $image_name,
            'type' => $this->mimeType,
            'tmp_name' => $tmp_filename,
            'size' => filesize($tmp_filename),
            'parent' => $this
        ]);

        $image->group = $transformation_group_name;

        $image->owner = $owner;

        $image->save(true);

        return $image;
    }

    protected function _cropCenter($transformation_name, $width, $height){
        return $this->_transform($transformation_name, "resizeDown($width, $height, 'outside')->crop('center', 'middle', $width, $height)");
    }

    /** @ORM\PrePersist */
    public function _prePersist($args = null){
        $app = App::i();
        

        $_hook_class = $this->getHookClassPath($this->owner->getClassName());
        $app->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').insert:before', $args);

        $app->storage->add($this);
    }
    /** @ORM\PostPersist */
    public function _postPersist($args = null){
        $_hook_class = $this->getHookClassPath($this->owner->getClassName());
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').insert:after', $args);

        if(!$this->_path){
            $this->getRelativePath();
            $this->save(true);
        }        
    }

    /** @ORM\PreRemove */
    public function _preRemove($args = null){
        $files = $this->repo()->findBy(['parent' => $this]);
        foreach($files as $f)
            $f->delete(true);

        $_hook_class = $this->getHookClassPath($this->owner->getClassName());
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').remove:before', $args);
    }
    /** @ORM\PostRemove */
    public function _postRemove($args = null){
        $app = App::i();
        $app->storage->remove($this);

        $_hook_class = $this->getHookClassPath($this->owner->getClassName());
        $app->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').remove:after', $args);
    }

    /** @ORM\PreUpdate */
    public function _preUpdate($args = null){
        $_hook_class = $this->getHookClassPath($this->owner->getClassName());
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').update:before', $args);
    }
    /** @ORM\PostUpdate */
    public function _postUpdate($args = null){
        $_hook_class = $this->getHookClassPath($this->owner->getClassName());
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').update:after', $args);
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
