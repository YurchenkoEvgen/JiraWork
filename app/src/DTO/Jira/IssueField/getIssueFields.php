<?php

namespace App\DTO\Jira\IssueField;

use App\DTO\Jira\JiraApiCore;
use App\DTO\Jira\JiraAPIInterface;
use App\Entity\IssueField;
use App\Repository\ProjectRepository;

class getIssueFields extends JiraApiCore implements JiraAPIInterface
{
    protected ?ProjectRepository $projectRepository;

    public function getData():array
    {
        $returned = [];
        if (isset($this->projectRepository)) {
            if ($this->setUri('/field')->sendRequest()){
                foreach ($this->getArray() as $value) {
                    $field = new IssueField();
                    $field->fillFromJira($value, $this->projectRepository);
                    $returned[] = $field;
                }
            } else {
                switch ($this->getResponseCode()) {
                    default:
                        $this->defaultError();
                }
            }
        } else {
            $this->addError('Not set repository');
        }

        return $returned;
    }

    public function setRepository(ProjectRepository $projectRepository):self
    {
        $this->projectRepository = $projectRepository;
        return $this;
    }
}