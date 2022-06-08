<?php

namespace App\Entity;

use App\Repository\IssueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueRepository::class)]
class Issue
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $summary;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    private $description;

    #[ORM\ManyToOne(targetEntity: Project::class, cascade: ['persist','merge'])]
    #[ORM\JoinColumn(nullable: false)]
    private $project;

    #[ORM\OneToMany(mappedBy: 'issue', targetEntity: IssueFieldValue::class, orphanRemoval: true)]
    private $issueFieldValues;

    public function __construct($data = null)
    {
        if (isset($data)) {
            if (is_array($data)) {
                $this->id=$data['id'];
                $this->summary=$data['fields']['summary'];
                $this->description = $data['fields']['description'];
                $this->project = new Project($data['fields']['project']);
            }
        }
        $this->issueFieldValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id):self
    {
        $this->id = $id;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function __toString(): string
    {
        return $this->summary. ' ('. $this->id.')';
    }

    public function getJiraArray():array
    {
        return [
            'summary' => $this->summary,
            'description' => $this->description
        ];
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
            $issueFieldValue->setIssue($this);
        }

        return $this;
    }

    public function removeIssueFieldValue(IssueFieldValue $issueFieldValue): self
    {
        if ($this->issueFieldValues->removeElement($issueFieldValue)) {
            // set the owning side to null (unless already changed)
            if ($issueFieldValue->getIssue() === $this) {
                $issueFieldValue->setIssue(null);
            }
        }

        return $this;
    }
}
