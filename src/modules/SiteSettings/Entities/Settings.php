<?php

namespace SiteSettings\Entities;

use MapasCulturais\App;
use MapasCulturais\Entity;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits\EntityMetadata;

/**
 * Entidade da tabela setting.
 *
 * Metadados editáveis (chave/valor) usam {@see SettingsMeta} via trait {@see EntityMetadata}.
 *
 * @property-read int $id
 * @property array $rawMetadata coluna JSON `metadata` no banco
 * @property int $status
 * @property \DateTime $createTimestamp
 * @property \DateTime|null $updateTimestamp
 * @property int|null $subsiteId
 */
#[ORM\Table(name: 'setting')]
#[ORM\Entity(repositoryClass: 'SiteSettings\Repositories\Settings')]
class Settings extends Entity
{
    use EntityMetadata;

    public static function getControllerClassName()
    {
        return \SiteSettings\Controller::class;
    }

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;

    protected $__enableMagicGetterHook = true;
    protected $__enableMagicSetterHook = true;

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'oc_setting_id_seq', allocationSize: 1, initialValue: 1)]
    protected $id;

    #[ORM\Column(name: 'status', type: 'smallint', nullable: false)]
    protected $status = self::STATUS_ACTIVE;

    #[ORM\Column(name: 'metadata', type: 'json', nullable: false)]
    protected $rawMetadata = [];

    #[ORM\Column(name: 'create_timestamp', type: 'datetime', nullable: false)]
    protected $createTimestamp;

    #[ORM\Column(name: 'update_timestamp', type: 'datetime', nullable: true)]
    protected $updateTimestamp;

    #[ORM\Column(name: 'subsite_id', type: 'integer', nullable: true)]
    protected $subsiteId;

    /**
     * @return \MapasCulturais\Entities\User
     */
    function getOwnerUser()
    {
        $app = App::i();
        return $app->user;;
    }

    /**
     * @return array<string, string>
     */
    public function fromToFilesMetadata(): array
    {
        return [
            'home-header' => 'bannerImageData',
            'home-opportunities' => 'entitiesOpportunityImageData',
            'home-events' => 'entitiesEventImageData',
            'home-spaces' => 'entitiesSpaceImageData',
            'home-agents' => 'entitiesAgentImageData',
            'home-projects' => 'entitiesProjectImageData',
            'home-register' => 'registerImageData',
            'logo-image' => 'imageLogoData',
            'favicon-svg' => 'faviconSvgData',
            'favicon-png' => 'faviconPngData',
            'share-image' => 'shareData',
            'mail-image' => 'mailImageData'
        ];
    }
}
