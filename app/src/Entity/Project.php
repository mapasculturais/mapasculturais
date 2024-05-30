<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EntityStatusEnum;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Subsite;

#[ORM\Entity]
#[ORM\Table(name: 'project')]
class Project
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'project_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id;

    #[ORM\Column(type: 'smallint')]
    private int $type;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', name: 'short_description', nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: 'text', name: 'long_description', nullable: true)]
    private ?string $longDescription = null;

    #[ORM\Column(type: DateTime::class, name: 'update_timestamp', nullable: true)]
    private ?DateTimeInterface $updateTimestamp = null;

    #[ORM\Column(type: DateTime::class, name: 'starts_on', nullable: true)]
    private ?DateTimeInterface $startsOn = null;

    #[ORM\Column(type: DateTime::class, name: 'ends_on', nullable: true)]
    private ?DateTimeInterface $endsOn = null;

    #[ORM\Column(type: DateTime::class, name: 'create_timestamp')]
    private DateTimeInterface $createTimestamp;

    #[ORM\Column(type: 'smallint')]
    private int $status;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', fetch: 'LAZY', cascade: ['remove'])]
    private iterable $children;

    #[ORM\ManyToOne(targetEntity: Agent::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Agent $owner = null;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'project', fetch: 'LAZY', cascade: ['persist'])]
    private iterable $events;

    #[ORM\OneToMany(targetEntity: ProjectOpportunity::class, mappedBy: 'ownerEntity', cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $relatedOpportunities;

    #[ORM\OneToMany(targetEntity: ProjectMeta::class, mappedBy: 'owner', cascade: ['remove', 'persist'], orphanRemoval: true)]
    private iterable $metadata;

    #[ORM\OneToMany(targetEntity: ProjectFile::class, mappedBy: 'owner', cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $files;

    #[ORM\OneToMany(targetEntity: ProjectAgentRelation::class, mappedBy: 'owner', cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $agentRelations;

    #[ORM\OneToMany(targetEntity: ProjectTermRelation::class, fetch: 'LAZY', mappedBy: 'owner', cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $termRelations;

    #[ORM\OneToMany(targetEntity: ProjectSealRelation::class, fetch: 'LAZY', mappedBy: 'owner', cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $sealRelations;

    #[ORM\OneToMany(targetEntity: ProjectPermissionCache::class, mappedBy: 'owner', cascade: ['remove'], orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private iterable $permissionsCache;

    #[ORM\Column(type: 'integer', name: 'subsite_id', nullable: true)]
    private ?int $subsiteId = null;

    #[ORM\ManyToOne(targetEntity: Subsite::class)]
    #[ORM\JoinColumn(name: 'subsite_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Subsite $subsite = null;

    public function __construct()
    {
        $this->status = EntityStatusEnum::ENABLED->getValue();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
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

    public function getUpdateTimestamp(): ?DateTimeInterface
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?DateTimeInterface $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }

    public function getStartsOn(): ?DateTimeInterface
    {
        return $this->startsOn;
    }

    public function setStartsOn(?DateTimeInterface $startsOn): void
    {
        $this->startsOn = $startsOn;
    }

    public function getEndsOn(): ?DateTimeInterface
    {
        return $this->endsOn;
    }

    public function setEndsOn(?DateTimeInterface $endsOn): void
    {
        $this->endsOn = $endsOn;
    }

    public function getCreateTimestamp(): DateTimeInterface
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(DateTimeInterface $createTimestamp): void
    {
        $this->createTimestamp = $createTimestamp;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): iterable
    {
        return $this->children;
    }

    public function getOwner(): ?Agent
    {
        return $this->owner;
    }

    public function setOwner(?Agent $owner): void
    {
        $this->owner = $owner;
    }

    public function getEvents(): iterable
    {
        return $this->events;
    }

    public function getRelatedOpportunities(): iterable
    {
        return $this->relatedOpportunities;
    }

    public function getMetadata(): iterable
    {
        return $this->metadata;
    }

    public function getFiles(): iterable
    {
        return $this->files;
    }

    public function getAgentRelations(): iterable
    {
        return $this->agentRelations;
    }

    public function getTermRelations(): iterable
    {
        return $this->termRelations;
    }

    public function getSealRelations(): iterable
    {
        return $this->sealRelations;
    }

    public function getPermissionsCache(): iterable
    {
        return $this->permissionsCache;
    }

    public function getSubsiteId(): ?int
    {
        return $this->subsiteId;
    }

    public function getSubsite(): ?Subsite
    {
        return $this->subsite;
    }

    public function setSubsite(?Subsite $subsite): void
    {
        $this->subsite = $subsite;
    }
}
