<?php

namespace App\DTO\Jira;

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

    public function sendRequest() {
        $this->jiraAPI->setJson($this->options);
        $this->jiraAPI->sendRequest();
        $this->resultRaw = $this->jiraAPI->getContent();
        $this->isValid = $this->jiraAPI->isValid();
        $this->resultCode = $this->jiraAPI->getCode();
        $this->resultArray = $this->jiraAPI->getContentAsArray();
        $this->jiraAPI->clear();
        return $this;
    }

    public function addOption(string $name, mixed $value,string $preefix = '') {
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
     * Add default 401 and other code
     *
     * @param $code
     * @return void
     */
    protected function defaultError($code) {
        switch ($code) {
            case 401:
                $this->addError('Authentication credentials are incorrect or missing.');
            default:
                $this->addError('Something wrong. Unclassified error.');
        }
    }
}