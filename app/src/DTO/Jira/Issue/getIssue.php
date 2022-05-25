<?php

namespace App\DTO\Jira\Issue;

use App\Entity\Issue;

class getIssue extends \App\DTO\Jira\JiraAPIInterfacesClass implements \App\DTO\Jira\JiraAPIInterface
{
    protected string $id;

    public function getData():Issue
    {
        $returned = new Issue();

        if (empty($this->id)){
            $this->addError('Not set ID ');
        } else {
            $this->jiraAPI->setUri('issue/'.$this->id);
            if ($this->sendRequest()->isValid) {
                $returned = new Issue($this->resultArray);
            } else {
                switch ($this->resultCode) {
                    case 404:
                        $this->addError('The issue is not found or the user does not have permission to view it.');
                        break;
                    default:
                        $this->defaultError($this->resultCode);
                }
            }
        }

        return $returned;
    }

    public function setID(string $ID){
        $this->id = $ID;
        return $this;
    }
}