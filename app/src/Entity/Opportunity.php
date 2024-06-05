<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EntityStatusEnum;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\OpportunityAgentRelation;
use MapasCulturais\Entities\OpportunityFile;
use MapasCulturais\Entities\OpportunityMeta;
use MapasCulturais\Entities\OpportunityPermissionCache;
use MapasCulturais\Entities\OpportunitySealRelation;
use MapasCulturais\Entities\OpportunityTermRelation;

#[ORM\Entity]
class Opportunity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'opportunity_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id;

    #[ORM\Column(name: 'type', type: 'smallint')]
    private int $type;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(name: 'short_description', type: 'text')]
    private string $shortDescription;

    #[ORM\Column(name: 'long_description', type: 'text')]
    private string $longDescription;

    #[ORM\Column(name: 'registration_from', type: 'datetime')]
    private DateTime $registrationFrom;

    #[ORM\Column(name: 'registration_to', type: 'datetime')]
    private DateTime $registrationTo;

    #[ORM\Column(name: 'published_registrations', type: 'boolean')]
    private bool $publishedRegistrations = false;

    #[ORM\Column(name: 'registration_categories', type: 'json', nullable: true)]
    private ?array $registrationCategories = [];

    #[ORM\Column(name: 'create_timestamp', type: 'datetime')]
    private DateTime $createTimestamp;

    #[ORM\Column(name: 'update_timestamp', type: 'datetime', nullable: true)]
    private ?DateTime $updateTimestamp;

    #[ORM\Column(name: 'publish_timestamp', type: 'datetime', nullable: true)]
    private ?DateTime $publishTimestamp;

    #[ORM\Column(name: 'auto_publish', type: 'boolean', options: ['default' => 'false'])]
    private bool $autoPublish;

    #[ORM\Column(name: 'status', type: 'smallint')]
    private int $status;

    #[ORM\ManyToOne(targetEntity: Opportunity::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Opportunity $parent;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Opportunity::class, cascade: ['remove'], fetch: 'LAZY')]
    private array $children = [];

    #[ORM\ManyToOne(targetEntity: Agent::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Agent $owner;

    #[ORM\OneToOne(mappedBy: 'opportunity', targetEntity: EvaluationMethodConfiguration::class)]
    private EvaluationMethodConfiguration $evaluationMethodConfiguration;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: OpportunityMeta::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private iterable $metadata;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: OpportunityFile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $files;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: OpportunityAgentRelation::class, cascade: ['remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $agentRelations;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: OpportunityTermRelation::class, cascade: ['remove'], fetch: 'LAZY', orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $termRelation;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: OpportunitySealRelation::class, cascade: ['remove'], fetch: 'LAZY', orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'object_id', onDelete: 'CASCADE')]
    private iterable $sealRelations;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: OpportunityPermissionCache::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private iterable $permissionsCache;

    #[ORM\Column(name: 'subsite_id', type: 'integer', nullable: true)]
    private int $subsite;

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

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    public function getLongDescription(): string
    {
        return $this->longDescription;
    }

    public function setLongDescription(string $longDescription): void
    {
        $this->longDescription = $longDescription;
    }

    public function getRegistrationFrom(): DateTime
    {
        return $this->registrationFrom;
    }

    public function setRegistrationFrom(DateTime $registrationFrom): void
    {
        $this->registrationFrom = $registrationFrom;
    }

    public function getRegistrationTo(): DateTime
    {
        return $this->registrationTo;
    }

    public function setRegistrationTo(DateTime $registrationTo): void
    {
        $this->registrationTo = $registrationTo;
    }

    public function isPublishedRegistrations(): bool
    {
        return $this->publishedRegistrations;
    }

    public function setPublishedRegistrations(bool $publishedRegistrations): void
    {
        $this->publishedRegistrations = $publishedRegistrations;
    }

    public function getRegistrationCategories(): ?array
    {
        return $this->registrationCategories;
    }

    public function setRegistrationCategories(?array $registrationCategories): void
    {
        $this->registrationCategories = $registrationCategories;
    }

    public function getUpdateTimestamp(): ?DateTime
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?DateTime $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }

    public function getPublishTimestamp(): ?DateTime
    {
        return $this->publishTimestamp;
    }

    public function setPublishTimestamp(?DateTime $publishTimestamp): void
    {
        $this->publishTimestamp = $publishTimestamp;
    }

    public function isAutoPublish(): bool
    {
        return $this->autoPublish;
    }

    public function setAutoPublish(bool $autoPublish): void
    {
        $this->autoPublish = $autoPublish;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getParent(): Opportunity
    {
        return $this->parent;
    }

    public function setParent(Opportunity $parent): void
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

    public function getOwner(): Agent
    {
        return $this->owner;
    }

    public function setOwner(Agent $owner): void
    {
        $this->owner = $owner;
    }

    public function getEvaluationMethodConfiguration(): EvaluationMethodConfiguration
    {
        return $this->evaluationMethodConfiguration;
    }

    public function setEvaluationMethodConfiguration(EvaluationMethodConfiguration $evaluationMethodConfiguration): void
    {
        $this->evaluationMethodConfiguration = $evaluationMethodConfiguration;
    }

    public function getMetadata(): iterable
    {
        return $this->metadata;
    }

    public function setMetadata(iterable $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getFiles(): iterable
    {
        return $this->files;
    }

    public function setFiles(iterable $files): void
    {
        $this->files = $files;
    }

    public function getAgentRelations(): iterable
    {
        return $this->agentRelations;
    }

    public function setAgentRelations(iterable $agentRelations): void
    {
        $this->agentRelations = $agentRelations;
    }

    public function getTermRelation(): iterable
    {
        return $this->termRelation;
    }

    public function setTermRelation(iterable $termRelation): void
    {
        $this->termRelation = $termRelation;
    }

    public function getSealRelations(): iterable
    {
        return $this->sealRelations;
    }

    public function setSealRelations(iterable $sealRelations): void
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

    public function getSubsite(): int
    {
        return $this->subsite;
    }

    public function setSubsite(int $subsite): void
    {
        $this->subsite = $subsite;
    }
}
