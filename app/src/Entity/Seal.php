<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Seal
{
    public const STATUS_RELATED = -1;
    public const STATUS_ENABLED = 1;
    public const STATUS_DRAFT = 0;
    public const STATUS_DISABLED = -9;
    public const STATUS_TRASH = -10;
    public const STATUS_ARCHIVED = -2;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'seal_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(type: 'text', name: 'short_description', nullable: true)]
    private string $shortDescription;

    #[ORM\Column(type: 'text', name: 'long_description', nullable: true)]
    private string $longDescription;

    #[ORM\Column(type: 'text', name: 'certificate_text', nullable: true)]
    private string $certificateText;

    #[ORM\Column(type: 'smallint', name: 'valid_period')]
    private int $validPeriod;

    #[ORM\Column(type: 'datetime', name: 'create_timestamp')]
    private DateTimeInterface $createTimestamp;

    #[ORM\Column(type: 'smallint')]
    private int $status = self::STATUS_ENABLED;

    #[ORM\Column(type: 'json', name: 'locked_fields', nullable: true, options: ['default' => '[]'])]
    private iterable $lockedFields;
}
