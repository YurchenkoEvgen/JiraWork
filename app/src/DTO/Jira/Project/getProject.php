<?php

namespace App\DTO\Project;

use App\DTO\Jira\JiraApiCore;
use App\DTO\Jira\JiraAPIInterface;
use App\Entity\Project;

class getProject extends JiraApiCore implements JiraAPIInterface
{
    private string $id;

    public function getData():Project
    {
        $returned = new Project();
        if (empty($this->id)) {
            $this->addError('No set ID project');
        } else {
            $this->setUri('project/'.$this->id);
            if ($this->sendRequest()) {
                $returned = new Project($this->getArray());
            } else {
                switch ($this->getResponseCode()) {
                    case 404:
                        $this->addError('The project is not found or the user does not have permission to view it');
                        break;
                    default:
                        $this->defaultError();
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