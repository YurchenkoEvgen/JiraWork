<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private $id=null;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: IssueField::class)]
    private $issueFields;

    public function __construct($data=null)
    {
        if (isset($data)) {
            if (is_array($data)) {
                $this->id = $data['id'];
                $this->name = $data['name'];
            }
        }
        $this->issueFields = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id) {
        $this->id = $id;
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

    public function __toString(): string
    {
        return $this->name. ' ('.$this->id.')';
    }

    /**
     * @return Collection<int, IssueField>
     */
    public function getIssueFields(): Collection
    {
        return $this->issueFields;
    }

    public function addIssueField(IssueField $issueField): self
    {
        if (!$this->issueFields->contains($issueField)) {
            $this->issueFields[] = $issueField;
            $issueField->setProject($this);
        }

        return $this;
    }

    public function removeIssueField(IssueField $issueField): self
    {
        if ($this->issueFields->removeElement($issueField)) {
            // set the owning side to null (unless already changed)
            if ($issueField->getProject() === $this) {
                $issueField->setProject(null);
            }
        }

        return $this;
    }
}
