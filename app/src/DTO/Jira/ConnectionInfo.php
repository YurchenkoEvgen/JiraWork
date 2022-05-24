<?php

namespace App\DTO\Jira;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class ConnectionInfo
{
    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     **/
    private $email;
    /**
     * @Assert\Length(min=24, max=24)
     */
    private $token;
    /**
     * @Assert\Url()
     */
    private $url;
    /**
     * @Assert\Url()
     */
    private $basicuri;

    private const prefix = 'rest/api/2/';

    public function __construct(Session $session)
    {
        $this->email = $session->get('auth_email');
        $this->token = $session->get('auth_token');
        $this->url = $session->get('auth_url');
        $this->basicuri = $this->url.$this::prefix;
    }

    public function isValid(): bool
    {
        return Validation::createValidatorBuilder()
                ->enableAnnotationMapping()
                ->addDefaultDoctrineAnnotationReader()
                ->getValidator()
                ->validate($this)->count() == 0;
    }

    public function getBasicuri(): string
    {
        return $this->basicuri;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getToken() {
        return $this->token;
    }

    public function getUrl() {
        return $this->url;
    }

}