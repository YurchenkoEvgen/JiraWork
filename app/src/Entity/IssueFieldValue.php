<?php

namespace App\Entity;

use App\Repository\IssueFieldValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Route;

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
    private ?string $value_string;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $value_float;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $value_date;

    #[ORM\ManyToOne(targetEntity: Project::class, cascade: ['persist', 'merge'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Project $value_project;

    #[ORM\ManyToOne(targetEntity: Issue::class, cascade: ['persist', 'merge'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Issue $value_issue;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist', 'merge'])]
    #[ORM\JoinColumn(referencedColumnName: 'account_id', nullable: true)]
    private ?User $value_user;

    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id):self
    {
        $this->id = $id;
        return $this;
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

        //SET TYPE
        $associations = array(
            'number' => 'float',
            'user' => User::class,
            'datetime' => \DateTime::class,
            'any' => 'serialize',
            'date' => 'date',
            'json' => 'json',
            'project' => Project::class,
            'issue' => Issue::class,
            'progress' => 'serialize'
        );

        $this->setType(
            (array_key_exists($this->issueFiled->getType(), $associations))?
                $associations[$this->issueFiled->getType()]:
                'string');
        $this->setIsArray($issueFiled->getIsArray());

        return $this;
    }

    public function getType(): ?string
    {
        if (empty($this->type) && isset($this->issueFiled)) {
            $this->setIssueFiled($this->issueFiled);
        }
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        //SET COLUMN
        $association = array(
            'float' => 'float',
            User::class => 'user',
            \DateTime::class => 'date',
            Project::class => 'project',
            Issue::class => 'issue',
        );

        $this->setDatacolumn(
            (array_key_exists($type,$association))?$association[$type]:'string'
        );

        return $this;
    }

    public function getDatacolumn(): string
    {
        return $this->datacolumn;
    }

    protected function setDatacolumn(string $datacolumn): self
    {

        $this->DataColumnIsValid($datacolumn);

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
        if ($this->DataColumnIsValid($dataColumn) && isset($this->{'value_'.$this->datacolumn})) {
            $data = $this->{'value_'.$this->datacolumn};
            if ($this->getType() == 'serialize') {
                $data = json_decode($data,false);
            }
            return $data;
        } else {
            return null;
        }
    }

    public function getValueForTemplate():mixed
    {
        $data = $this->{'value_'.$this->datacolumn};
        return ($this->datacolumn == 'date' && $data instanceof \DateTimeInterface)?
            $data->format(\DateTime::ATOM):$data;
    }

    public function getRouteValue():string
    {
        if (!empty($this->getValue()) ) {
            switch ($this->datacolumn) {
                case 'issue':
                    return 'app_issue_show';
                case 'project':
                    return 'app_project_show';
                case 'user':
                    return 'app_user_show';
            }
        }

        return '';
    }

    public function setValue(mixed $value):self
    {
        if (isset($value) && $this->DataColumnIsValid()) {
            if (is_object($value)?($this->getType() != $value::class):(gettype($value) != $this->getType())) {
                switch ($this->getType()) {
                    case 'float':
                        $dbvalue = (float)$value;
                        break;
                    case \DateTime::class:
                        $dbvalue = new \DateTime($value);
                        $dbvalue->setTimezone(new \DateTimeZone('UTC'));
                        break;
                    case User::class:
                        $dbvalue = new User($value);
                        break;
                    case Project::class:
                        $dbvalue = new Project($value);
                        break;
                    case Issue::class:
                        $dbvalue = new Issue();
                        $dbvalue->setProject($this->getIssue()->getProject());
                        $dbvalue->importFromJira($value);
                        break;
                    case 'serialize':
                        $dbvalue = json_encode($value,JSON_UNESCAPED_UNICODE);
                        break;
                    default:
                        $dbvalue = substr(print_r($value,true), 0, 2048);
                }
            } else {
                $dbvalue = $value;
            }
            $this->{'value_' . $this->datacolumn} = $dbvalue;
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->issueFiled.'_'.$this->issue;
    }
}
