<?php

namespace App\Entity;

use App\Repository\IssueFieldRepository;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueFieldRepository::class)]
class IssueField
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $_key;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'boolean')]
    private $custom;

    #[ORM\ManyToOne(targetEntity: Project::class, cascade: ['persist'], inversedBy: "issueFields")]
    private $project;

    #[ORM\Column(type: 'string', length: 255)]
    private $clauseNames;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $type;

    #[ORM\OneToMany(mappedBy: 'issueFiled', targetEntity: IssueFieldValue::class, orphanRemoval: true)]
    private $issueFieldValues;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isArray;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $searchable;

    private bool $needProject;

    public function __construct()
    {
        $this->issueFieldValues = new ArrayCollection();
        $this->needProject = false;
        $this->searchable = false;
        $this->isArray = false;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id):self
    {
        $this->id = $id;
        return $this;
    }

    public function getKey(): ?string
    {
        return $this->_key;
    }

    public function setKey(string $_key): self
    {
        $this->_key = $_key;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name??'';
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isCustom(): ?bool
    {
        return $this->custom;
    }

    public function setCustom(bool $custom): self
    {
        $this->custom = $custom;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function setClauseNames($clauseNames):self
    {
        if (is_array($clauseNames) && !empty($clauseNames)) {
            $this->clauseNames = $clauseNames[0];
        } elseif (is_string($clauseNames)) {
            $this->clauseNames = $clauseNames;
        } else {
            $this->clauseNames = '';
        }

        return $this;
    }

    public function getClauseNames():string
    {
        return $this->clauseNames;
    }

    public function getIsArray():bool
    {
        return $this->isArray;
    }

    public function setIsArray(bool $isArray):self
    {
        $this->isArray = $isArray;
        return $this;
    }

    public function getSearchable():bool
    {
        return $this->searchable;
    }

    public function setSearchable(bool $searchable):self
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function getNeedProject():bool
    {
        return $this->needProject;
    }

    public function fillFromJira(array $data, ProjectRepository $projectRepository):self
    {
        $this->type = 'string';

        $this->id = $data['id'];
        $this->_key = $data['key'];
        $this->name = $data['name'];
        $this->custom = $data['custom'];
        $this->setClauseNames($data['clauseNames']);
        if (key_exists('schema',$data)){
            $this->type = $data['schema']['type'];
            if ($this->type == 'array') {
                $this->isArray = true;
                $this->type =$data['schema']['items'];
            }
        }
        $this->searchable = $data['searchable'];

        //No declared and fix
        if (in_array($this->id,['subtasks','parent']) ) {
            $this->type = 'issue';
        }

        if (key_exists('scope',$data) && $data['scope']['type'] == 'PROJECT') {
            $this->project = $projectRepository->find($data['scope']['project']['id']);
            $this->needProject = is_null($this->project);
        }

        return $this;
    }

    public function getType():string
    {
        return $this->type??'';
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Collection<int, IssueFieldValue>
     */
    public function getIssueFieldValues(): Collection
    {
        return $this->issueFieldValues;
    }

    public function addIssueFieldValue(IssueFieldValue $issueFieldValue): self
    {
        if (!$this->issueFieldValues->contains($issueFieldValue)) {
            $this->issueFieldValues[] = $issueFieldValue;
            $issueFieldValue->setIssueFiled($this);
        }

        return $this;
    }

    public function removeIssueFieldValue(IssueFieldValue $issueFieldValue): self
    {
        if ($this->issueFieldValues->removeElement($issueFieldValue)) {
            // set the owning side to null (unless already changed)
            if ($issueFieldValue->getIssueFiled() === $this) {
                $issueFieldValue->setIssueFiled(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
