<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
//    #[ORM\Id]
//    #[ORM\GeneratedValue]
//    #[ORM\Column(type: 'integer')]
//    private $id;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 50)]
    private $accountId;

    #[ORM\Column(type: 'string', length: 250)]
    #[Assert\Url]
    private string $_self;

    #[ORM\Column(type: 'string', length: 50)]
    private string $accountType;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    private $emailAddress;

    #[ORM\Column(type: 'string', length: 255)]
    private $displayName;

    #[ORM\Column(type: 'boolean')]
    private $active;

    public function __construct(array $data)
    {
        $keysAssociation = array(
            'accountId'=>'accountId',
            'self'=>'_self',
            'accountType'=>'accountType',
            'emailAddress'=>'emailAddress',
            'displayName'=>'displayName',
            'active'=>'active',
        );

        foreach ($keysAssociation as $key=>$property) {
            if (array_key_exists($key, $data)) {
                $this->{$property} = $data[$key];
            }
        }
    }

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): self
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function getSelf():string
    {
        return $this->_self;
    }

    public function setSelf(string $uri):self
    {
        $this->_self = $uri;
        return $this;
    }

    public function setAccountType(string $accountType):self
    {
        $this->accountType = $accountType;
        return $this;
    }

    public function getAccountType():string
    {
        return  $this->accountType;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
