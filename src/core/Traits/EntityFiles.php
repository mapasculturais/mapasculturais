<?php
namespace MapasCulturais\Traits;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Entities\File;

/**
 * Defines that the entity has files.
 *
 * Use this trait in entities that has files. The file groups must be registered.
 *
 * <code>
 * // example of $entity->files
 * array(
 *      'avatar' => [ Files  ],
 *      'downloads' => [  Files  ],
 * )
 * </code>
 *
 * @property-read \MapasCulturais\Entities\File[] $files Files of this entities grouped by file groups.
 * @property-read string $fileClassName
 *
 * @see \MapasCulturais\Definitions\FileGroup
 * @see \MapasCulturais\App::registerFileGroup()
 */
trait EntityFiles {

    use EntityFilesFunctions;

    #[ORM\OneToMany(targetEntity: self::class . "File", mappedBy: "owner", cascade: ["remove"], orphanRemoval: true)]
    #[ORM\JoinColumn(name: "id", referencedColumnName: "object_id", onDelete: "CASCADE")]
    protected $__files;

    /**
     * This entity uses files
     * @return bool true
     */
    public static function usesFiles(){
        return true;
    }
}