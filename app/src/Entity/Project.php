<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    //#[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: 'integer')]
    private $id=null;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    public function __construct($data=null)
    {
        if (isset($data)) {
            if (is_array($data)) {
                $this->id = $data['id'];
                $this->name = $data['name'];
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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
}
