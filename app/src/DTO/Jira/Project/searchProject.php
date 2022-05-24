<?php

namespace App\DTO\Jira\Project;

use App\DTO\Jira\JiraAPIInterface;
use App\DTO\Jira\JiraAPIInterfacesClass;
use App\Entity\Project;

class searchProject extends JiraAPIInterfacesClass implements JiraAPIInterface
{

    public function getData(bool $returnObject = true)
    {
        $this->jiraAPI->setUri('project/search');
        $this->sendRequest();
        $return = [];
        if ($this->isValid) {
            if ($returnObject) {
                foreach ($this->resultArray['values'] as $value){
                    $project = new Project();
                    $project->setId($value['id'])->setName('name');
                    $return[] = $project;
                }
            } else {
                $return = $this->resultArray['values'];
            }
        }
        return $return;
    }
}