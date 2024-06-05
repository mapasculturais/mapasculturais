<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EntityStatusEnum;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Subsite;

#[ORM\Entity]
#[ORM\Table(name: 'events')]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'event_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Project::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Project $project = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', name: 'short_description')]
    private string $shortDescription;

    #[ORM\Column(type: 'text', name: 'long_description', nullable: true)]
    private ?string $longDescription = null;

    #[ORM\Column(type: 'text', name: 'rules', nullable: true)]
    private ?string $rules = null;

    #[ORM\Column(type: 'smallint')]
    private int $status;

    #[ORM\Column(type: 'integer', name: 'agent_id')]
    private int $agentId;

    #[ORM\Column(type: 'boolean', name: 'is_verified')]
    private bool $isVerified;

    #[ORM\Column(type: 'smallint', name: 'type')]
    private int $type;

    #[ORM\Column(type: DateTime::class, name: 'create_timestamp')]
    private DateTimeInterface $createTimestamp;

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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
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

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(?string $longDescription): void
    {
        $this->longDescription = $longDescription;
    }

    public function getRules(): string
    {
        return $this->rules;
    }

    public function setRules(string $rules): void
    {
        $this->rules = $rules;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getAgentId(): int
    {
        return $this->agentId;
    }

    public function setAgentId(int $agentId): void
    {
        $this->agentId = $agentId;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
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
