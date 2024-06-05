<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EntityStatusEnum;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities\AgentAgentRelation;
use MapasCulturais\Entities\AgentFile;
use MapasCulturais\Entities\AgentMeta;
use MapasCulturais\Entities\AgentOpportunity;
use MapasCulturais\Entities\AgentPermissionCache;
use MapasCulturais\Entities\AgentSealRelation;
use MapasCulturais\Entities\AgentTermRelation;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Subsite;
use MapasCulturais\Entities\User;
use MapasCulturais\Types\GeoPoint;

#[ORM\Entity]
class Agent
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'agent_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id;

    #[ORM\Column(name: 'type', type: 'smallint')]
    private int $type;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(name: 'public_location', type: 'boolean', nullable: true)]
    private ?bool $publicLocation = null;

    #[ORM\Column(name: 'location', type: 'point')]
    private GeoPoint $location;

    #[ORM\Column(name: '_geo_location', type: 'geography')]
    private string $geoLocation;

    #[ORM\Column(name: 'short_description', type: 'text', nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(name: 'long_description', type: 'text', nullable: true)]
    private ?string $longDescription = null;

    #[ORM\Column(name: 'create_timestamp', type: 'datetime')]
    private DateTime $createTimestamp;

    #[ORM\Column(type: 'smallint')]
    private int $status;

    #[ORM\ManyToOne(targetEntity: Agent::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private Agent $parent;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Agent::class, cascade: ['remove'], fetch: 'LAZY')]
    private array $children = [];

    #[ORM\ManyToMany(targetEntity: User::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Space::class, cascade: ['remove'], orphanRemoval: true)]
    private iterable $spaces;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Project::class, cascade: ['remove'], orphanRemoval: true)]
    private iterable $project;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AgentOpportunity::class, cascade: ['remove'], orphanRemoval: true)]
    private iterable $ownedOpportunities;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Event::class, cascade: ['remove'], orphanRemoval: true)]
    private iterable $events;

    #[ORM\OneToMany(mappedBy: 'ownerEntity', targetEntity: AgentOpportunity::class, cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private array $relatedOpportunities = [];

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AgentMeta::class, cascade: ['remove, persist'], orphanRemoval: true)]
    private iterable $metadata;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AgentFile::class, cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private array $files = [];

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AgentAgentRelation::class, cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private array $agentRelations = [];

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AgentTermRelation::class, cascade: ['remove'], fetch: 'LAZY', orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private array $termRelations = [];

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AgentSealRelation::class, cascade: ['remove'], fetch: 'LAZY', orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private array $sealRelations = [];

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AgentPermissionCache::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private iterable $permissionsCache;

    #[ORM\Column(name: 'update_timestamp', type: 'datetime', nullable: true)]
    private ?DateTime $updateTimestamp = null;

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

    public function getPublicLocation(): ?bool
    {
        return $this->publicLocation;
    }

    public function setPublicLocation(?bool $publicLocation): void
    {
        $this->publicLocation = $publicLocation;
    }

    public function getLocation(): GeoPoint
    {
        return $this->location;
    }

    public function setLocation(GeoPoint $location): void
    {
        $this->location = $location;
    }

    public function getGeoLocation(): string
    {
        return $this->geoLocation;
    }

    public function setGeoLocation(string $geoLocation): void
    {
        $this->geoLocation = $geoLocation;
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

    public function getParent(): ?Agent
    {
        return $this->parent;
    }

    public function setParent(?Agent $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getSpaces(): iterable
    {
        return $this->spaces;
    }

    public function setSpaces(iterable $spaces): void
    {
        $this->spaces = $spaces;
    }

    public function getProject(): iterable
    {
        return $this->project;
    }

    public function setProject(iterable $project): void
    {
        $this->project = $project;
    }

    public function getOwnedOpportunities(): iterable
    {
        return $this->ownedOpportunities;
    }

    public function setOwnedOpportunities(iterable $ownedOpportunities): void
    {
        $this->ownedOpportunities = $ownedOpportunities;
    }

    public function getEvents(): iterable
    {
        return $this->events;
    }

    public function setEvents(iterable $events): void
    {
        $this->events = $events;
    }

    public function getRelatedOpportunities(): array
    {
        return $this->relatedOpportunities;
    }

    public function setRelatedOpportunities(array $relatedOpportunities): void
    {
        $this->relatedOpportunities = $relatedOpportunities;
    }

    public function getMetadata(): iterable
    {
        return $this->metadata;
    }

    public function setMetadata(iterable $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function getAgentRelations(): array
    {
        return $this->agentRelations;
    }

    public function setAgentRelations(array $agentRelations): void
    {
        $this->agentRelations = $agentRelations;
    }

    public function getTermRelations(): array
    {
        return $this->termRelations;
    }

    public function setTermRelations(array $termRelations): void
    {
        $this->termRelations = $termRelations;
    }

    public function getSealRelations(): array
    {
        return $this->sealRelations;
    }

    public function setSealRelations(array $sealRelations): void
    {
        $this->sealRelations = $sealRelations;
    }

    public function getPermissionsCache(): iterable
    {
        return $this->permissionsCache;
    }

    public function setPermissionsCache(iterable $permissionsCache): void
    {
        $this->permissionsCache = $permissionsCache;
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
