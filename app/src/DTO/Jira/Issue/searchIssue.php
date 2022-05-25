<?php

namespace App\DTO\Jira\Issue;

use App\Entity\Issue;

class searchIssue extends \App\DTO\Jira\JiraAPIInterfacesClass implements \App\DTO\Jira\JiraAPIInterface
{

    public function getData():array
    {
        $returned = [];

        $this->jiraAPI->setUri('search')->setMethod('POST');
        if ($this->sendRequest()->isValid) {
            foreach ($this->resultArray['issues'] as $value) {
                $returned[] = new Issue($value);
            }
        } else {
            switch ($this->resultCode) {
                case 400:
                    $this->addError('JQL query is invalid');
                    break;
                default:
                    $this->defaultError($this->resultCode);
            }
        }

        return $returned;
    }
}