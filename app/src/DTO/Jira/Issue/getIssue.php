<?php

namespace App\DTO\Jira\Issue;

use App\DTO\Jira\JiraApiCore;
use App\Entity\Issue;

class getIssue extends JiraApiCore implements \App\DTO\Jira\JiraAPIInterface
{
    protected string $id;

    public function getData():Issue
    {
        $returned = new Issue();

        if (empty($this->id)){
            $this->addError('Not set ID ');
        } else {
            $this->setUri('issue/'.$this->id);
            if ($this->sendRequest()) {
                $returned = new Issue($this->getArray());
            } else {
                switch ($this->getResponseCode()) {
                    case 404:
                        $this->addError('The issue is not found or the user does not have permission to view it.');
                        break;
                    default:
                        $this->defaultError();
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