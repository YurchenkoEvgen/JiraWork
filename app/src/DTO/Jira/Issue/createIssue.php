<?php

namespace App\DTO\Jira\Issue;

use App\DTO\Jira\JiraApiCore;
use App\Entity\Issue;

class createIssue extends JiraApiCore implements \App\DTO\Jira\JiraAPIInterface
{
    private ?Issue $issue;

    public function getData():?Issue
    {
        if (isset($this->issue)) {
            $this
                ->setUri('issue')
                ->setMethod('POST')
                ->setValidcodes('201')
                ->addOption('fields',$this->issue->getJiraArray());
            if ($this->sendRequest()) {
                $this->extractData();
            } else {
                switch ($this->getResponseCode()) {
                    case 400:
                        $this->addError('is missing required fields.
contains invalid field values.
contains fields that cannot be set for the issue type.
is by a user who does not have the necessary permission.
is to create a subtype in a project different that of the parent issue.
is for a subtask when the option to create subtasks is disabled.
is invalid for any other reason.');
                        break;
                    case 403:
                        $this->addError('The user does not have the necessary permission.');
                        break;
                    default:
                        $this->defaultError();
                }
            }
        } else {
            $this->addError('Not Issue set');
        }

        return $this->issue;
    }

    public function extractData():?Issue
    {
        if ($this->hasData()){
            $this->issue->setId($this->getArray()['id']);
        }

        return $this->issue;
    }

    public function setIssue(Issue $issue):self
    {
        $this->issue = $issue;
        return $this;
    }
}