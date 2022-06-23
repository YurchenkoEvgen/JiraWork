<?php

namespace App\DTO\Jira;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class JiraApiCore implements JiraAPIInterface
{
    protected array $arg;
    protected string $basicUri;
    protected string $method;
    protected string $uri;

    protected array $validcodes;

    protected int $responseCode;
    protected bool $isValid;
    protected string $responseBody;
    protected array $responseBodyInArray;
    protected bool $hasData;

    protected array $errors;
    protected array $postloaders;

    //CREATE FUNCTION
    public function __construct(ConnectionInfo $connectionInfo)
    {
        $this->isValid = $connectionInfo->isValid();
        if ($this->isValid) {
            $this->arg = [
                'auth_basic' => [
                    $connectionInfo->getEmail(),
                    $connectionInfo->getToken()
                ],
                'query' => []
            ];
            $this->basicUri = $connectionInfo->getBasicuri();
            $this->method = Request::METHOD_GET;
            $this->validcodes = ['200'];
            $this->errors = [];
            $this->responseCode = 0;
            $this->responseBody = '';
            $this->responseBodyInArray = [];
            $this->hasData = false;
            $this->postloaders = [];
        } else {
            $this->addError('Wrong connection setting');
        }
    }

    public static function getInterface(ConnectionInfo $connectionInfo)
    {
        return new static($connectionInfo);
    }

    //GETTERS
    public function getError(): array
    {
        return $this->errors;
    }

    public function getContent():string
    {
        return $this->responseBody;
    }

    public function getArray():array
    {
        return $this->responseBodyInArray;
    }

    public function getResponseCode():int
    {
        return $this->responseCode;
    }

    public function hasData():bool
    {
        return $this->hasData;
    }

    public function getPostload():array
    {
        return $this->postloaders;
    }

    //STATE
    public function isValid():bool
    {
        return $this->isValid;
    }

    public function hasErrors(?int$code = null):bool
    {
        return (isset($code))?array_key_exists($code,$this->getError()):count($this->getError())>0;
    }

    public function hasPostload():bool
    {
        return count($this->getPostload()) > 0;
    }

    //SETTERS
    public function addError($content, $code = 0):self {
        $this->errors[$code] = new \Error($content,$code);
        return $this;
    }

    protected function setMethod(string $method):self {
        $this->method = $method;
        return $this;
    }

    protected function setUri(string $uri):self {
        $uri = str_replace($this->basicUri,'',$uri);
        $this->uri = $this->basicUri.$uri;
        return $this;
    }

    protected function setValidcodes(string $codes):self
    {
        $this->validcodes = explode(',',$codes);
        return $this;
    }

    //FUNCTIONS
    /**
     * Add default 401, 405 and other code
     *
     * @param $code
     * @return void
     */
    protected function defaultError() {
        switch ($this->responseCode) {
            case 401:
                $this->addError('Authentication credentials are incorrect or missing.', 401);
                break;
            case 405:
                $this->addError('Method not Allowed', 405);
                break;
            default:
                $this->addError('Something wrong. Unclassified error. HTTP code '.$this->getResponseCode(), $this->getResponseCode());
        }
    }

    /**
     * Send request and return isValid()
     *
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function sendRequest():bool {
        if ($this->isValid()) {
            //fix array query
            $query = '';
            foreach ($this->arg['query'] as $key=>$item) {
                if (is_array($item)) {
                    $query .= $key.'='.implode('&'.$key.'=',$item);
                    unset($this->arg['query'][$key]);
                }
            }
            if ($query != '') {
                $separator = (strpos($this->uri,'?'))?'&':'?';
                $this->setUri($this->uri.$separator.$query);
            }

            $result = HttpClient::create()->request($this->method,$this->uri,$this->arg);
            try {
                $this->responseCode = $result->getStatusCode();
            } catch (TransportExceptionInterface $e) {
                $this->addError($e->getMessage());
            }

            $this->isValid = in_array((string)$this->responseCode,$this->validcodes);

            if ($this->isValid()) {
                $this->responseBody = $result->getContent();
                if ($this->responseBody) {
                    $this->responseBodyInArray = $result->toArray();
                }
            }
        }
        $this->hasData = $this->isValid();
        return $this->hasData;
    }

    public function addOption(string $name, mixed $value,string $preefix = ''):self
    {
        $options = (array_key_exists('json',$this->arg)?$this->arg['json']:[]);

        if (array_key_exists($name,$options)) {
            if (is_array($options[$name]) && is_array($value)) {
                $options[$name] = array_merge($options[$name], $value);
            } elseif (is_string($options[$name]) && is_string($value)) {
                $options[$name] .= ' '.$preefix.' '.$value;
            }
        } else {
            $options[$name] = $value;
        }
        $this->arg['json'] = $options;
        return $this;
    }

    public function updOption(string $name, mixed $value):self
    {
        $options = (array_key_exists('json',$this->arg)?$this->arg['json']:[]);
        $options[$name] = $value;
        $this->arg['json'] = $options;
        return $this;
    }

    public function updQuery(string $name, mixed $value):self
    {
        $options = $this->arg['query'];
        $options[$name] = $value;
        $this->arg['query'] = $options;
        return $this;
    }

    protected function addPostload($object, ?object $filter=null):self
    {
        if (is_object($object) && mb_substr(get_class($object),0,10) == 'App\Entity') {
            $this->postloaders[] = PostLoader::GetPostLoaderEntry($object,$filter);
        }
        return $this;
    }

    public function getData()
    {
        return [];
    }

    public function extractData()
    {
        return [];
    }
}