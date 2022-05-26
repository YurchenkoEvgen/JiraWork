<?php

namespace App\DTO\Jira;


use Symfony\Component\HttpFoundation\Session\Session;

class JiraAPIInterfacesClass
{
    protected JiraAPI $jiraAPI;
    protected string $resultRaw;
    protected array $resultArray;
    protected bool $isValid;
    protected int $resultCode;
    protected array $options;
    protected array $errors;

    public function __construct(JiraAPI $jiraAPI)
    {
        $this->jiraAPI = $jiraAPI;
        $this->jiraAPI->clear();
        $this->arrayResult = [];
        $this->options = [];
        $this->errors = [];
    }


    public static function getInterface(JiraAPI $jiraAPI){
        return new static($jiraAPI);
    }

    public static function getInterfaceSingle(Session $session) {
        return new static(JiraAPI::GetAPIBuilder($session));
    }

    /**
     * Prepare body, send request, fill result.
     * If use APIclass try getData()!
     * @return $this
     */
    public function sendRequest() {
        $this->jiraAPI->setJson($this->options);
        $this->jiraAPI->sendRequest();
        $this->resultRaw = $this->jiraAPI->getContent();
        $this->isValid = $this->jiraAPI->isValid();
        $this->resultCode = $this->jiraAPI->getCode();
        $this->resultArray = $this->jiraAPI->getContentAsArray();
        return $this;
    }

    public function addOption(string $name, mixed $value,string $preefix = ''):self {
        if (array_key_exists($name,$this->options)) {
            if (is_array($this->options[$name]) && is_array($value)) {
                $this->options[$name] = array_merge($this->options[$name], $value);
            } elseif (is_string($this->options[$name]) && is_string($value)) {
                $this->options[$name] .= ' '.$preefix.' '.$value;
            }
        } else {
            $this->options[$name] = $value;
        }
        return $this;
    }

    public function isValid() {
        return $this->isValid;
    }

    public function getError( ) {
        return $this->errors;
    }

    protected function addError($content) {
        $this->errors[] = $content;
        return $this;
    }

    /**
     * Add default 401, 405 and other code
     *
     * @param $code
     * @return void
     */
    protected function defaultError() {
        switch ($this->resultCode) {
            case 401:
                $this->addError('Authentication credentials are incorrect or missing.');
                break;
            case 405:
                $this->addError('Method not Allowed');
                break;
            default:
                $this->addError('Something wrong. Unclassified error.');
        }
    }
}