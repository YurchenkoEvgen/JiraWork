<?php

namespace App\DTO;

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
        $this->method = 'GET';
        $this->setUri('/');
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

    public function sendRequest(string $validecodes = '200') {
        dump($this);
        $result = $this->cli->request($this->method,$this->uri,$this->arg);
        $this->responseCode = $result->getStatusCode();

        $validcodearray = explode(',',$validecodes);
        $this->isvalid = in_array((string)$this->responseCode,$validcodearray);

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

    public function clearResponse()
    {
        $this->responseCode = null;
        $this->responseBody = null;
        $this->isvalid = null;
    }

    public function GetProjects() {
        $this->setMethod('GET');
        $this->setUri('project');

        if ($this->setMethod('GET')->setUri('project')->sendRequest()->isValid()) {
            return $this->getContentAsArray();
        } else {
            return null;
        }
    }
}