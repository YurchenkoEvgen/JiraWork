<?php

namespace App\DTO\Jira\Issue;

use App\DTO\Jira\JiraApiCore;
use App\DTO\Jira\ObjectPagination;
use App\Entity\Issue;
use App\DTO\Jira\JiraAPIInterface;
use App\Entity\IssueField;
use Doctrine\Persistence\ManagerRegistry;

class searchIssue extends JiraApiCore implements JiraAPIInterface
{
    use ObjectPagination;
    private ManagerRegistry $_em;
    private bool $addRelation = true;

    public function getData():array
    {
        $returned = [];

        $this
            ->setUri('search')
            ->setMethod('POST')
        ;
        do {
            if ($this->sendRequest()) {
                $returned += $this->extractData();
            } else {
                switch ($this->getResponseCode()) {
                    case 400:
                        $this->addError('JQL query is invalid', 400);
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

    public function extractData():array
    {
        $result = [];
        if ($this->hasData()){
            foreach ($this->getArray()['issues'] as $value) {
                $issue = new Issue();
                $issue->importFromJira($value, $this->_em);
                $result[] = $issue;
                if ($issue->getUncomplateFileds()) {
                    $this->addPostload(new IssueField());
                }
                if ($this->addRelation) {
                    foreach ($issue->getIssueFieldValues() as $issueFieldValue) {
//                        $this->addPostload($issueFieldValue->getValue());
                    }
                    $this->addRelation = false;
                }
            }
        }

        return $result;
    }
    public function addFilter (string $condition, string $type = 'AND'):self {
        return $this->addOption('jql', $condition, $type);
    }

    public function setManagerRegistry(ManagerRegistry $managerRegistry):self
    {
        $this->_em = $managerRegistry;
        return $this;
    }

    public function byID(array $ids):self
    {
        return $this->addFilter("id IN (".implode(',',$ids).")");
    }
}