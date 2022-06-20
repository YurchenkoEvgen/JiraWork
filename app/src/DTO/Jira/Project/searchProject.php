<?php

namespace App\DTO\Jira\Project;

use App\DTO\Jira\JiraApiCore;
use App\DTO\Jira\JiraAPIInterface;;
use App\Entity\Project;

class searchProject extends JiraApiCore implements JiraAPIInterface
{

    public function getData():array
    {
        $return = [];

        $this->setUri('project/search');
        if ($this->sendRequest()) {
            $return = $this->extractData();
        } else {
            switch ($this->getResponseCode()) {
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

    public function extractData():array
    {
        $result = [];
        if ($this->hasData()) {
            foreach ($this->getArray()['values'] as $value) {
                $result[] = new Project($value);
            }
        }

        return $result;
    }
}