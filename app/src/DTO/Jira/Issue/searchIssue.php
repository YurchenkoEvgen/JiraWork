<?php

namespace App\DTO\Jira\Issue;

use App\DTO\Jira\JiraApiCore;
use App\Entity\Issue;

class searchIssue extends JiraApiCore implements \App\DTO\Jira\JiraAPIInterface
{

    public function getData():array
    {
        $returned = [];

        $this
            ->setUri('search')
            ->setMethod('POST');
        if ($this->sendRequest()) {
            foreach ($this->getArray()['issues'] as $value) {
                $returned[] = new Issue($value);
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

        return $returned;
    }

    public function addFilter (string $condition, string $type = 'AND'):self {
        return $this->addOption('jql', $condition, $type);
    }
}