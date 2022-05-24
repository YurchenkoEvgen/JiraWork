<?php

namespace App\DTO\Jira;

interface JiraAPIInterface
{
    public function getData(bool $returnObject = false);
}