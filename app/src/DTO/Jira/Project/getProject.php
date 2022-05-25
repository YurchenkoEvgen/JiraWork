<?php

namespace App\DTO\Project;

use App\DTO\Jira\JiraAPIInterface;
use App\DTO\Jira\JiraAPIInterfacesClass;
use App\Entity\Project;

class getProject extends JiraAPIInterfacesClass implements JiraAPIInterface
{
    private string $id;

    public function getData():Project
    {
        $returned = new Project();
        if (empty($this->id)) {
            $this->addError('No set ID project');
        } else {
            $this->jiraAPI->setUri('project/'.$this->id);
            if ($this->sendRequest()->isValid) {
                $returned = new Project($this->resultArray);
            } else {
                switch ($this->resultCode) {
                    case 404:
                        $this->addError('The project is not found or the user does not have permission to view it');
                        break;
                    default:
                        $this->defaultError($this->resultCode);
                }
            }
        }

        return $returned;
    }

    public function setID(string $id) {
        $this->id = $id;
        return $this;
    }
}