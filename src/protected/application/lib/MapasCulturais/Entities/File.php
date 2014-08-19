<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;

/**
 * File
 *
 * @property-read int $id File Id
 * @property-read string $md5 File MD5
 * @property-read string $mimeType File Mime Type
 * @property-read string $name File name
 * @property-read string $group File Group (gallery|avatar|download|etc.)
 * @property-read string $objectType File Owner Class Name
 * @property-read id $objectId File Owner Id
 * @property-read \DateTime $createTimestamp File Create Timestamp
 * @property-read \MapasCulturais\Entity $owner The Owner of this File
 *
 * @property-read array $tmpFile $_FILE
 * @ORM\Table(name="file")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\File")
 * @ORM\HasLifecycleCallbacks
 */
class File extends \MapasCulturais\Entity
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
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_type", type="string", nullable=false)
     */
    protected $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * The owner entity of this file
     * @var \MapasCulturais\Entity
     */
    protected $_owner;

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
    protected $tmpFile = array('name' => '', 'type' => '', 'tmp_name' => '', 'size' => 0);

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

        parent::__construct();
    }

    protected function canUserCreate($user){
        if($this->owner && $this->owner->className == $this->className)
            return true;
        else
            return $this->owner->canUser('modify');
    }

    protected function canUserRemove($user){
        return $this->owner->canUser('modify');
    }

    protected function canUserModify($user){
        return $this->owner->canUser('modify');
    }

    public function save($flush = false) {
        if(preg_match('#.php$#', $this->mimeType))
            throw new \MapasCulturais\Exceptions\PermissionDenied($this->ownerUser, $this, 'save');
            
        parent::save($flush);
    }

    public function jsonSerialize() {

        return array(
            'id' => $this->id,
            'md5' => $this->md5,
            'mimeType' => $this->mimeType,
            'name' => $this->name,
            'description' => $this->description,
            'group' => $this->group,
            'files' => $this->files,
            'url' => $this->url,
            'deleteUrl' => $this->deleteUrl,
        );
    }


    public function getGroup(){
        return trim($this->group);
    }

    public function setGroup($val){
        $this->group = trim($val);
    }

    /**
     * Returns the owner of this metadata
     * @return \MapasCulturais\Entity
     */
    public function getOwner(){
        if(!$this->_owner && ($this->objectType && $this->objectId))
            $this->_owner = App::i()->repo($this->objectType)->find($this->objectId);

        return $this->_owner;
    }

    /**
     * Set the owner of this metadata
     * @param \MapasCulturais\Entity $owner
     */
    public function setOwner(\MapasCulturais\Entity $owner){
        $this->_owner = $owner;
        $this->objectType = $owner->className;
        $this->objectId = $owner->id;
    }

    /**
     * Returns the url to this file
     * @return string the url to this file
     */
    public function getUrl(){
        return App::i()->storage->getUrl($this);
    }

    public function getPath(){
        return App::i()->storage->getPath($this);
    }

    public function transform($transformation_name){
        if(!preg_match('#^image/#i',$this->mimeType))
                return null;

        $wideimage_operations = App::i()->getRegisteredImageTransformation($transformation_name);

        if(preg_match('#^cropCenter[ ]*\([ ]*(\d+)[ ]*,[ ]*(\d+)[ ]*\)$#', $wideimage_operations, $match)){
            return $this->_cropCenter($transformation_name, $match[1], $match[2]);
        }else{
            return $this->_transform($transformation_name, $wideimage_operations);
        }
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

        $wideimage_operations = strtolower(str_replace(' ', '', $wideimage_operations));

        $hash = md5($this->md5 . $this->name . $wideimage_operations);

        // modify the filename adding the hash before the file extension. ex: file.png => file-5762a89ee8e05021006d6c35095903b5.png
        $image_name = preg_replace("#(\.[[:alnum:]]+)$#i", '-' . $hash . '$1', $this->name);

        if(key_exists($transformation_name, $this->files))
            return $this->files[$transformation_name];

        if(!file_exists($this->getPath()))
            return $this;

        $new_image = \WideImage\WideImage::load($this->getPath());

        eval('$new_image = $new_image->' . $wideimage_operations . ';');

        $tmp_filename = sys_get_temp_dir() . '/' . $image_name;

        $new_image->saveToFile( $tmp_filename );

        $image = new File(array(
            'error' => UPLOAD_ERR_OK,
            'name' => $image_name,
            'type' => $this->mimeType,
            'tmp_name' => $tmp_filename,
            'size' => filesize($tmp_filename)
        ));

        $image->group = $transformation_name;

        $image->setOwner($this);

        $image->save(true);

        return $image;
    }

    protected function _cropCenter($transformation_name, $width, $height){
        return $this->_transform($transformation_name, "resizeDown($width, $height, 'outside')->crop('center', 'middle', $width, $height)");
    }


    /** @ORM\PostLoad */
    public function _postLoad($args = null){
        $this->group = trim($this->group);
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').load', $args);
    }

    /** @ORM\PrePersist */
    public function _prePersist($args = null){
        App::i()->storage->add($this);

        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').insert:before', $args);
    }
    /** @ORM\PostPersist */
    public function _postPersist($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').insert:after', $args);

        $this->owner->clearFilesCache();
    }

    /** @ORM\PreRemove */
    public function _preRemove($args = null){
        $files = $this->repo()->findBy(array('objectType' => __CLASS__, 'objectId' => $this->id));
        foreach($files as $f)
            $f->delete(true);

        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').remove:before', $args);
    }
    /** @ORM\PostRemove */
    public function _postRemove($args = null){
        App::i()->storage->remove($this);

        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').remove:after', $args);

        $this->owner->clearFilesCache();
    }

    /** @ORM\PreUpdate */
    public function _preUpdate($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').update:before', $args);
    }
    /** @ORM\PostUpdate */
    public function _postUpdate($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').file(' . $this->group . ').update:after', $args);

        $this->owner->clearFilesCache();
    }


    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PostLoad */
    public function postLoad($args = null){ parent::postLoad($args); }

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
