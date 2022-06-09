<?php

namespace App\Entity;

use App\Repository\IssueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IssueRepository::class)]
class Issue
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 20)]
    private string $_key;

    #[ORM\Column(type: 'string', length: 1024)]
    #[Assert\Url]
    private string $_self;

    #[ORM\Column(type: 'string', length: 255)]
    private $summary;

    #[ORM\ManyToOne(targetEntity: Project::class, cascade: ['persist','merge'])]
    #[ORM\JoinColumn(nullable: false)]
    private $project;

    #[ORM\OneToMany(mappedBy: 'issue', targetEntity: IssueFieldValue::class, orphanRemoval: true)]
    private $issueFieldValues;

    public function __construct()
    {
        $this->issueFieldValues = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id):self
    {
        $this->id = $id;
        return $this;
    }

    public function get_key():string
    {
        return $this->_key;
    }

    public function set_key(string $key):self
    {
        $this->_key = $key;
        return $this;
    }

    public function get_self():string
    {
        return $this->_self;
    }

    public function set_self(string $uri):self
    {
        $this->_self = $uri;
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
        return $this->getSummary(). ' ('. $this->id.')';
    }

    public function importFromJira(array $data):self
    {
        if (
            empty(array_diff_key(array_fill_keys(['id','self','key','fields'],null),$data)) &&
            is_array($data['fields']) &&
            empty(array_diff_key(array_fill_keys(['summary','project'],null),$data['fields']))
        ) {
            $this->id = $data['id'];
            $this->_self = $data['self'];
            $this->_key = $data['key'];
            $this->summary = $data['fields']['summary'];
            $this->project = new Project($data['fields']['project']);
        }

        return $this;
    }

    public function getJiraArray():array
    {
        return [
            'summary' => $this->summary,
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
