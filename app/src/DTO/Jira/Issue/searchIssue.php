<?php

namespace App\DTO\Jira\Issue;

use App\DTO\Jira\JiraApiCore;
use App\DTO\Jira\ObjectPagination;
use App\Entity\Issue;
use App\DTO\Jira\JiraAPIInterface;
use Doctrine\Persistence\ManagerRegistry;

class searchIssue extends JiraApiCore implements JiraAPIInterface
{
    use ObjectPagination;
    private ManagerRegistry $_em;

    public function getData():array
    {
        $returned = [];

        $this
            ->setUri('search')
            ->setMethod('POST')
            ->addOption('maxResults',10)
        ;
        do {
            if ($this->sendRequest()) {
                foreach ($this->getArray()['issues'] as $value) {
                    $issue = new Issue();
                    $issue->importFromJira($value, $this->_em);
                    $returned[] = $issue;
                }
            } else {
                switch ($this->getResponseCode()) {
                    case 400:
                        $this->addError('JQL query is invalid');
                        break;
                    default:
                        $this->defaultError();
                }
            }
            if($this->haveNext()) {
                $options = $this->getPaginationParams();
                foreach ($options as $key=>$opt) {
                    $this->updOption($key,$opt);
                }
            }
        } while (!$this->hasErrors() && $this->haveNext());

        return $returned;
    }

    public function addFilter (string $condition, string $type = 'AND'):self {
        return $this->addOption('jql', $condition, $type);
    }

    public function setManagerRegistry(ManagerRegistry $managerRegistry):self
    {
        $this->_em = $managerRegistry;
        return $this;
    }
}