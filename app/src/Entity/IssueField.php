<?php

namespace App\Entity;

use App\Repository\IssueFieldRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueFieldRepository::class)]
class IssueField
{
    #[ORM\Id]
//    #[ORM\GeneratedValue]
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
        return $this->name;
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

    public function fillFromJira(array $data, ProjectRepository $projectRepository):self
    {
        $this->id = $data['id'];
        $this->_key = $data['key'];
        $this->name = $data['name'];
        $this->custom = $data['custom'];
        $this->setClauseNames($data['clauseNames']);
        if (key_exists('schema',$data)){
            $this->type = $data['schema']['type'];
            if ($this->type == 'array') {
                $this->type .=':'.$data['schema']['items'];
            }
        } elseif ($this->name == 'parent') {
            $this->type = 'IssueField';
        }

        if (key_exists('scope',$data) && $data['scope']['type'] == 'PROJECT') {
            $this->project = $projectRepository->find($data['scope']['project']['id']);
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
}
