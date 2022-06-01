<?php

namespace App\DTO\Jira\Issue;

use App\DTO\Jira\JiraApiCore;
use App\Entity\Issue;

class editIssue extends JiraApiCore implements \App\DTO\Jira\JiraAPIInterface
{
    private Issue $issue;

    public function getData():bool
    {
        $returned = false;
        if (empty($this->issue->getId())) {
            $this->addError('Not set the Issue');
        } else {
            $this
                ->setMethod('PUT')
                ->setUri('issue/'.$this->issue->getId())
                ->setValidcodes('204')
                ->addOption('fields',$this->issue->getJiraArray());
            if ($this->sendRequest()) {
                $returned = true;
            } else {
                switch ($this->getResponseCode()) {
                    case 400:
                        $this->addError("The request body is missing.
The user does not have the necessary permission to edit one or more fields.
The request includes one or more fields that are not found or are not associated with the issue's edit screen.
The request includes an invalid transition.");
                        break;
                    case 403:
                        $this->addError("Returned if the user uses overrideScreenSecurity or overrideEditableFlag but doesn't have the necessary permission.");
                        break;
                    case 404:
                        $this->addError("The issue is not found or the user does not have permission to view it.");
                        break;
                    default:
                        $this->defaultError();
                }
            }
        }

        return $returned;
    }

    public function setIssue(Issue $issue):self
    {
        $this->issue = $issue;
        return $this;
    }
}