<?php

namespace PersonalAccessToken\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Traits;

#[ORM\Table(name: "personal_access_token")]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
#[ORM\HasLifecycleCallbacks]
class PersonalAccessToken extends \MapasCulturais\Entity
{
    use Traits\EntitySoftDelete,
        Traits\EntityRevision;

    public const TOKEN_PREFIX = 'mc_pat_';
    public const TOKEN_BYTES = 32;

    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: "personal_access_token_id_seq", allocationSize: 1, initialValue: 1)]
    protected $id;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\User")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $user;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: false)]
    protected $name;

    #[ORM\Column(name: "token_hash", type: "string", length: 128, nullable: false)]
    protected $tokenHash;

    #[ORM\Column(name: "token_prefix", type: "string", length: 16, nullable: false)]
    protected $tokenPrefix;

    #[ORM\Column(name: "permissions", type: "json", nullable: false)]
    protected $permissions = [];

    #[ORM\Column(name: "last_used_at", type: "datetime", nullable: true)]
    protected $lastUsedAt;

    #[ORM\Column(name: "expires_at", type: "datetime", nullable: true)]
    protected $expiresAt;

    #[ORM\Column(name: "create_timestamp", type: "datetime", nullable: false)]
    protected $createTimestamp;

    #[ORM\Column(name: "update_timestamp", type: "datetime", nullable: true)]
    protected $updateTimestamp;

    #[ORM\Column(name: "status", type: "smallint", nullable: false)]
    protected $status = self::STATUS_ENABLED;

    private string $_plainTextToken = '';

    static function getControllerId()
    {
        return 'personal-access-token';
    }

    public static function getEntityTypeLabel($plural = false): string
    {
        return $plural
            ? i::__('Tokens de Acesso Pessoal')
            : i::__('Token de Acesso Pessoal');
    }

    static function getValidations()
    {
        return [
            'name' => [
                'required' => i::__('O nome do token é obrigatório'),
                'v::stringType()->length(3, 255)' => i::__('O nome deve ter entre 3 e 255 caracteres'),
            ],
            'permissions' => [
                'v::arrayType()->length(1,null)' => i::__('Ao menos uma permissão deve ser informada'),
            ],
        ];
    }

    public static function getPropertiesMetadata($include_column_name = false)
    {
        $result = parent::getPropertiesMetadata();
        unset($result['status']['options']['draft']);
        return $result;
    }

    public static function generateToken(): string
    {
        return self::TOKEN_PREFIX . bin2hex(random_bytes(self::TOKEN_BYTES));
    }

    public function createToken(): string
    {
        $plainText = self::generateToken();
        $this->tokenHash = hash('sha256', $plainText);
        $this->tokenPrefix = substr($plainText, 0, strlen(self::TOKEN_PREFIX) + 4);
        $this->_plainTextToken = $plainText;
        return $plainText;
    }

    public static function verifyToken(string $plainText): ?self
    {
        $app = App::i();

        if (!str_starts_with($plainText, self::TOKEN_PREFIX)) {
            return null;
        }

        $hash = hash('sha256', $plainText);

        $qb = $app->em->createQueryBuilder();
        $qb->select('t')
            ->from(self::class, 't')
            ->where('t.tokenHash = :hash')
            ->andWhere('t.status = :status')
            ->setParameter('hash', $hash)
            ->setParameter('status', self::STATUS_ENABLED)
            ->setMaxResults(1);

        $token = $qb->getQuery()->getOneOrNullResult();

        if (!$token) {
            return null;
        }

        if ($token->isExpired()) {
            return null;
        }

        return $token;
    }

    public function touch(): void
    {
        $app = App::i();
        $conn = $app->em->getConnection();
        $conn->executeStatement(
            'UPDATE personal_access_token SET last_used_at = NOW() WHERE id = :id',
            ['id' => $this->id]
        );
        $this->lastUsedAt = new \DateTime();
    }

    public function isExpired(): bool
    {
        if (!$this->expiresAt) {
            return false;
        }
        return $this->expiresAt < new \DateTime();
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getPlainTextToken(): string
    {
        return $this->_plainTextToken;
    }

    public function getTokenMask(): string
    {
        return $this->tokenPrefix . str_repeat('*', 16);
    }

    protected function canUserCreate($user)
    {
        if ($user->is('guest')) {
            return false;
        }
        return $user->equals($this->user);
    }

    protected function canUserView($user)
    {
        if ($user->is('guest')) {
            return false;
        }
        return $user->equals($this->user);
    }

    protected function canUserModify($user)
    {
        if ($user->is('guest')) {
            return false;
        }
        return $user->equals($this->user);
    }

    protected function canUserRemove($user)
    {
        if ($user->is('guest')) {
            return false;
        }
        return $user->equals($this->user);
    }

    protected function canUserDestroy($user)
    {
        if ($user->is('guest')) {
            return false;
        }
        return $user->equals($this->user);
    }

    #[ORM\PrePersist]
    public function prePersist($args = null) { parent::prePersist($args); }
    #[ORM\PostPersist]
    public function postPersist($args = null) { parent::postPersist($args); }
    #[ORM\PreRemove]
    public function preRemove($args = null) { parent::preRemove($args); }
    #[ORM\PostRemove]
    public function postRemove($args = null) { parent::postRemove($args); }
    #[ORM\PreUpdate]
    public function preUpdate($args = null) { parent::preUpdate($args); }
    #[ORM\PostUpdate]
    public function postUpdate($args = null) { parent::postUpdate($args); }
}
