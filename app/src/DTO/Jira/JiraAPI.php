<?php

namespace App\DTO\Jira;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Session\Session;

class JiraAPI
{
    protected CurlHttpClient $cli;
    protected array $arg;
    protected string $method;
    protected string $uri;
    protected string $baseUri;

    protected int $responseCode;
    protected string $responseBody;
    protected bool $isvalid;
    protected array $responseBodyInArray;
    protected array $validcodes;

    public function __construct(Session $session)
    {
        $connectioninfo = new ConnectionInfo($session);
        $this->cli = HttpClient::create();
        $this->arg = [
            'auth_basic'=>[
                $connectioninfo->getEmail(),
                $connectioninfo->getToken()
            ]
        ];
        $this->baseUri = $connectioninfo->getBasicuri();
        $this->setDefaultData();
    }

    public function setDefaultData()
    {
        $this->responseCode = 0;
        $this->responseBody = '';
        $this->responseBodyInArray = [];
        $this->isvalid = false;
        $this->method = 'GET';
        $this->setUri('/');
        $this->validcodes = ['200'];
        return $this;
    }

    public function clear() {
        $this->setDefaultData();
        $this->arg = [
            'auth_basic'=>$this->arg['auth_basic']
        ];
    }

    static function GetAPIBuilder(Session $session) {
        return new JiraAPI($session);
    }

    public function setMethod(string $method) {
        $this->method = $method;
        return $this;
    }

    public function setUri(string $uri) {
        $this->uri = $this->baseUri.$uri;
        return $this;
    }

    public function setJson($value) {
        $this->arg['json'] = $value;
        return $this;
    }

    public function setValidcodes(string $validcodes) {
        $this->validcodes = explode(',',$validcodes);
        return $this;
    }

    public function sendRequest() {
        $result = $this->cli->request($this->method,$this->uri,$this->arg);
        $this->responseCode = $result->getStatusCode();

        $this->isvalid = in_array((string)$this->responseCode,$this->validcodes);

        if ($this->isvalid) {
            $this->responseBody = $result->getContent();
            if ($this->responseBody) {
                $this->responseBodyInArray = $result->toArray();
            }

        }

        return $this;
    }

    public function isValid() {
        return $this->isvalid;
    }

    public function getContent() {
        return $this->responseBody;
    }

    public function getContentAsArray() {
        return $this->responseBodyInArray;
    }

    public function getCode() {
        return $this->responseCode;
    }

}