<?php

namespace App\DTO\Jira\Issue;

use App\Entity\Issue;

class deleteIssue extends \App\DTO\Jira\JiraAPIInterfacesClass implements \App\DTO\Jira\JiraAPIInterface
{
    private Issue $issue;

    public function getData():bool
    {
        $returned = false;

        if (empty($this->issue->getId())) {
            $this->addError('No set ID');
        } else {
            $this->jiraAPI
                ->setUri('issue/'.$this->issue->getId())
                ->setMethod('DELETE')
                ->setValidcodes('204');
            if ($this->sendRequest()->isValid) {
                $returned = true;
            } else {
                switch ($this->resultCode) {
                    case 400:
                        $this->addError('The issue has subtasks and deleteSubtasks is not set to true.');
                        break;
                    case 403:
                        $this->addError('The user does not have permission to delete the issue.');
                        break;
                    case 404:
                        $this->addError('The issue is not found or the user does not have permission to view the issue.');
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