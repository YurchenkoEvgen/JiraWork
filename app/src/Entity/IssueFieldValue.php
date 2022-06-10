<?php

namespace App\Entity;

use App\Repository\IssueFieldValueRepository;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueFieldValueRepository::class)]
class IssueFieldValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Issue::class, inversedBy: 'issueFieldValues')]
    #[ORM\JoinColumn(nullable: false)]
    private Issue $issue;

    #[ORM\ManyToOne(targetEntity: IssueField::class, inversedBy: 'issueFieldValues')]
    #[ORM\JoinColumn(nullable: false)]
    private IssueField $issueFiled;

    #[ORM\Column(type: 'string', length: 50)]
    private string $datacolumn;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    private string $value_string;

    #[ORM\Column(type: 'float', nullable: true)]
    private float $value_float;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $value_date;

    #[ORM\ManyToOne(targetEntity: Project::class, cascade: ['persist', 'merge'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Project $value_project;

    #[ORM\ManyToOne(targetEntity: Issue::class, cascade: ['persist', 'merge'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Issue $value_issue;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist', 'merge'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $value_user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function setIssue(?Issue $issue): self
    {
        $this->issue = $issue;

        return $this;
    }

    public function getIssueFiled(): ?IssueField
    {
        return $this->issueFiled;
    }

    public function setIssueFiled(?IssueField $issueFiled): self
    {
        $this->issueFiled = $issueFiled;

        $associations = array(
            'number' => 'float',
            'user' => User::class,
            'datetime' => 'datetime',
            'any' => 'serialize',
            'date' => 'date',
            'json' => 'json',
            'project' => Project::class,
            'issue' => Issue::class
        );

        $this->setType(
            (array_key_exists($this->issueFiled->getType(), $associations))?
                $associations[$this->issueFiled->getType()]:
                'string');

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        $association = array(
            'float' => 'float',
            User::class => 'user',
            'datetime' => 'date'
        );

        return $this;
    }

    public function getDatacolumn(): string
    {
        return $this->datacolumn;
    }

    public function setDatacolumn(string $datacolumn): self
    {
        $this->datacolumn = $datacolumn;

        return $this;
    }

    private function DataColumnIsValid(?string $dataColumn = null) {
        if (empty($this->datacolumn) && isset($dataColumn)) {
            $this->datacolumn = $dataColumn;
        }
        return isset($this->datacolumn);
    }

    public function getIsArray(): bool
    {
        return $this->isArray;
    }

    public function setIsArray(bool $isArray):self
    {
        $this->isArray = $isArray;
        return $this;
    }

    public function getValue(?string $dataColumn = null):mixed
    {
        if ($this->DataColumnIsValid($dataColumn)) {
            return $this->{'value_'.$this->datacolumn};
        } else {
            return null;
        }
    }

    public function setValue(mixed $value, ?string $dataColumn = null):self
    {
        if ($this->DataColumnIsValid($dataColumn)) {
            $this->{'value_'.$this->datacolumn} = $value;
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->issueFiled.'_'.$this->issue;
    }
}
