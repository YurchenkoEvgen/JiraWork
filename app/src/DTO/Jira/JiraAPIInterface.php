<?php

namespace App\DTO\Jira;

interface JiraAPIInterface
{
    public function getData();

    public function extractData();
}