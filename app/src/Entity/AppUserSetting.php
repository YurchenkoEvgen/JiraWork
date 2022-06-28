<?php

namespace App\Entity;

use App\Repository\AppUserSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppUserSettingRepository::class)]
class AppUserSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: AppUser::class, inversedBy: 'appUserSettings')]
    #[ORM\JoinColumn(nullable: false)]
    private $AppUser;

    #[ORM\Column(type: 'string', length: 50)]
    private $setting;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppUser(): ?AppUser
    {
        return $this->AppUser;
    }

    public function setAppUser(?AppUser $AppUser): self
    {
        $this->AppUser = $AppUser;

        return $this;
    }

    public function getSetting(): ?string
    {
        return $this->setting;
    }

    public function setSetting(string $setting): self
    {
        $this->setting = $setting;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
