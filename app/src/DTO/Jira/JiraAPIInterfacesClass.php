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

    public function __construct(JiraAPI $jiraAPI)
    {
        $this->jiraAPI = $jiraAPI;
        $this->arrayResult = [];
        $this->options = [];
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
    }

    public function addOption(string $name, mixed $value) {
        if (array_key_exists($name,$this->options)) {
            if (is_array($this->options[$name]) && is_array($value)) {
                $this->options[$name] = array_merge($this->options[$name], $value);
            } elseif (is_string($this->options[$name]) && is_string($value)) {
                $this->options[$name] .= $value;
            }
        } else {
            $this->options[$name] = $value;
        }
        return $this;
    }
}