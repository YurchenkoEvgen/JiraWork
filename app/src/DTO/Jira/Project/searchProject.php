<?php

namespace App\DTO\Jira\Project;

use App\DTO\Jira\JiraAPIInterface;
use App\DTO\Jira\JiraAPIInterfacesClass;
use App\Entity\Project;

class searchProject extends JiraAPIInterfacesClass implements JiraAPIInterface
{

    public function getData(bool $returnObject = true):array
    {
        $return = [];

        $this->jiraAPI->setUri('project/search');
        if ($this->sendRequest()->isValid) {
            foreach ($this->resultArray['values'] as $value) {
                $return[] = new Project($value);
            }
        } else {
            switch ($this->resultCode) {
                case 400:
                    $this->addError('Request is not valid.');
                    break;
                case 404:
                    $this->addError('No projects matching the search criteria are found');
                    break;
                default:
                    $this->defaultError();
            }
        }
        return $return;
    }
}