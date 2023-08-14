<?php
namespace UserManagement\Entities;

use MapasCulturais\i;
use MapasCulturais\Traits;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * SystemRole
 *
 * @property-read \MapasCulturais\Definitions\Role $definition
 * 
 * @ORM\Table(name="system_role")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class SystemRole extends \MapasCulturais\Entity {
    use Traits\EntitySoftDelete,
        Traits\EntityDraft;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="system_role_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;
    

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=64, nullable=false)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="subsite_context", type="boolean", nullable=false)
     */
    protected $subsiteContext = true;

    
    /**
     * @var object
     *
     * @ORM\Column(name="permissions", type="json", nullable=true)
     */
    protected $permissions = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    public static function getEntityTypeLabel($plural = false): string {
        if ($plural)
            return i::__('Funções de usuário');
        else
            return i::__('Função de usuário');
    }

    /**
     * Retorna um array com as regras de validação
     * @return string[][] 
     */
    static function getValidations() {
        $app = App::i();
        $validations = [
            'name' => [
                'required' => i::__('O nome da função é obrigatório'),
                'unique' => i::__('O nome da função já está sendo utilizado'),
            ],
            'slug' => [
                'required' => i::__('O slug da função é obrigatório')
            ],
            'permissions' => [
                'v::arrayType()->length(1,null)' => i::__('Ao menos uma permissão deve ser informada')
            ]
        ];

        $prefix = self::getHookPrefix();
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    public static function getPropertiesMetadata($include_column_name = false) {
        $result = parent::getPropertiesMetadata();
        unset($result['status']['options']['draft']);
        return $result;
    }

    /**
     * Define o nome do role.
     * 
     * um slug será gerado a partir do nome.
     * 
     * @param mixed $name 
     * @return void 
     */
    protected function setName($name) {
        $app = App::i();
        $this->name = $name;
        $this->slug = $app->slugify($name);
    }


    /**
     * Verifica se o usuário pode criar a role
     */
    protected function canUserCreate($user) {
        return $user->is('saasAdmin');
    }

    /**
     * Verifica se o usuário pode modificar a role
     */
    protected function canUserModify($user) {
        return $user->is('saasAdmin');
    }

    /**
     * Verifica se o usuário pode remover a role
     */
    protected function canUserRemove($user) {
        return $user->is('saasAdmin');
    }
}