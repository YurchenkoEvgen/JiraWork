<?php

namespace App\DTO\Jira\Project;

use App\DTO\Jira\JiraApiCore;
use App\DTO\Jira\JiraAPIInterface;;
use App\DTO\Jira\ObjectPagination;
use App\Entity\Project;

class searchProject extends JiraApiCore implements JiraAPIInterface
{
    use ObjectPagination;

    public function getData():array
    {
        $return = [];

        $this->setUri('project/search');
        do {
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
            if($this->haveNext()) {
                $options = $this->getPaginationParams();
                foreach ($options as $key=>$opt) {
                    $this->updQuery($key,$opt);
                }
            }
        } while (!$this->hasErrors() && $this->haveNext());

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

    public function byID(array $ids):self
    {
        $this->updQuery('id', $ids);
        return $this;
    }
}