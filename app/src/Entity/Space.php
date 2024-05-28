<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EntityStatusEnum;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Types\GeoPoint;

#[ORM\Entity]
#[ORM\Table(name: 'spaces')]
class Space
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'space_id_seq', allocationSize: 1, initialValue: 1)]
    public int $id;

    #[ORM\ManyToOne(targetEntity: Space::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Space $parent = null;

    #[ORM\Column(name: 'location', type: 'point', nullable: false)]
    private GeoPoint $location;

    #[ORM\Column(name: '_geo_location', type: 'geography', nullable: false)]
    private $geoLocation;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'short_description', type: 'text', nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(name: 'long_description', type: 'text', nullable: true)]
    private ?string $longDescription = null;

    #[ORM\Column(name: 'create_timestamp', type: 'datetime', nullable: false)]
    private DateTimeInterface $createTimestamp;

    #[ORM\Column(name: 'status', type: 'smallint', nullable: false)]
    private int $status;

    #[ORM\Column(name: 'type', type: 'smallint', nullable: false)]
    private int $type;

    #[ORM\ManyToOne(targetEntity: Agent::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Agent $owner = null;

    #[ORM\Column(name: 'is_verified', type: 'boolean')]
    private bool $isVerified;

    #[ORM\Column(name: 'public', type: 'boolean', nullable: false)]
    private bool $public = false;

    #[ORM\Column(name: 'subsite_id', type: 'integer', nullable: true)]
    private ?int $subsiteId = null;

    public function __construct()
    {
        $this->status = EntityStatusEnum::ENABLED->getValue();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParent(): ?Space
    {
        return $this->parent;
    }

    public function setParent(?Space $parent): void
    {
        $this->parent = $parent;
    }

    public function getLocation(): GeoPoint
    {
        return $this->location;
    }

    public function setLocation(GeoPoint $location): void
    {
        $this->location = $location;
    }

    public function getGeoLocation()
    {
        return $this->geoLocation;
    }

    public function setGeoLocation($geoLocation): void
    {
        $this->geoLocation = $geoLocation;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(?string $longDescription): void
    {
        $this->longDescription = $longDescription;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getOwner(): ?Agent
    {
        return $this->owner;
    }

    public function setOwner(?Agent $owner): void
    {
        $this->owner = $owner;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function getSubsiteId(): ?int
    {
        return $this->subsiteId;
    }

    public function setSubsiteId(?int $subsiteId): void
    {
        $this->subsiteId = $subsiteId;
    }
}
