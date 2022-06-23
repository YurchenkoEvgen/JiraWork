<?php

namespace App\DTO\Jira;

use App\DTO\Jira\Issue\searchIssue;
use App\DTO\Jira\IssueField\getIssueFields;
use App\DTO\Jira\Project\searchProject;
use App\Entity\Issue;
use App\Entity\IssueField;
use App\Entity\IssueFieldValue;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class PostLoader
{
    protected ManagerRegistry $managerRegistry;
    protected ConnectionInfo $connectionInfo;
    protected JiraApiCore $jiraApiCore;

    protected array $tasks;
    protected array $loadedObject;

    public function __construct(
        JiraApiCore $jiraApiCore,
        ManagerRegistry $managerRegistry,
        ConnectionInfo $connectionInfo
    )
    {
        $this->managerRegistry = $managerRegistry;
        $this->connectionInfo = $connectionInfo;
        $this->jiraApiCore = $jiraApiCore;

        $this->tasks = [];
    }

    protected function AddTask(array $postloaders):self
    {
        $postloaders = array_unique($postloaders,SORT_REGULAR);
        foreach ($postloaders as $value) {
            if (!array_key_exists($value->code%10, $this->tasks)){//***0 update all
                if (array_key_exists($value->code, $this->tasks)) {
                    $this->tasks[$value->code][] = $value->id;
                } else {
                    $this->tasks[$value->code] = [$value->id];
                }
            }
        }
        krsort($this->tasks);
        return $this;
    }

    // FLUSH REDUILD RECURSIVE
    public function doPostLoad():array
    {
        $postextract = [];
        $this->AddTask($this->jiraApiCore->getPostload());
        foreach ($this->jiraApiCore->extractData() as $item) {
            $this->loadedObject[] = $this->GetPostLoaderEntry($item);
        }
        array_unique($this->loadedObject);
        unset($item);
        while (count($this->tasks) > 0) {
            $entityRegistery = null;
            $code = array_key_first($this->tasks);
            $ids = $this->tasks[$code];
            unset($this->tasks[$code]);
            $worker = $this->loadData($code,$ids);
            $result = $worker->getData();
            if ($worker->hasErrors()) {
                foreach ($worker->getError() as $key=>$error) {
                    $this->jiraApiCore->addError($error->getMessage(),$key);
                }
                return [];
            }
            if ($worker->hasPostload()) {
                $postloads = $worker->getPostload();
                $this->AddTask($postloads);
                $postextract[] = (object)[
                    'code' => min(array_column($postloads,'code')),
                    'obj' => $worker,
                    ];
            } else {
                foreach ($result as $value) {
                    $entityRegistery = $this->managerRegistry->getRepository($value::class);
                    $entityRegistery->merge($value);
                }
                if ($entityRegistery != null) {
                    $entityRegistery->flush();
                    $entityRegistery = null;
                }
            }

            //postextract working
            $trigered = array_filter($postextract,function ($k) use ($code) {return $k->code == $code;});
            if (count($trigered)) {
                foreach ($trigered as $extract) {
                    $data = $extract->obj->extractData();
                    foreach ($data as $value) {
                        $entityRegistery = $this->managerRegistry->getRepository($value::class);
                        $entityRegistery->merge($value);
                    }
                }
                $entityRegistery?->flush();
            }
        }

        return $this->jiraApiCore->extractData();
    }

    public function loadData(int $code, array $ids):JiraApiCore
    {
        switch ($code){
            case 1001:
                $fields = searchProject::getInterface($this->connectionInfo);
                $fields->byID($ids);
                return $fields;
            case 1200:
                $fields = getIssueFields::getInterface($this->connectionInfo);
                $fields->setRepository(new ProjectRepository($this->managerRegistry));
                return $fields;
            case 1301:
                return searchIssue::getInterface($this->connectionInfo)
                    ->byID($ids)
                    ->setManagerRegistry($this->managerRegistry);
            default:
                return new JiraApiCore($this->connectionInfo);
        }
    }

    public static function GetPostLoaderEntry(object $object, ?object $filter=null):object
    {
        $result = ['code'=>null,'id'=>null];
        $id = $object->getId();
        switch (get_class($object)){
            case Project::class: //100*
                return (object)['code'=>1000+(bool)$id,'id'=>$id];
            case User::class: //110*
                return (object)['code'=>1100+(bool)$id,'id'=>$id];
            case IssueField::class: //120* Jira junkie!!!
                return (object)['code'=>1200,'id'=>null];
            case Issue::class: //130*
                if (!is_null($filter) && get_class($filter) == Project::class) {
                    return (object)['code'=>1305,'id'=>$filter->getId()];
                }
                return (object)['code'=>1300+(bool)$id,'id'=>$id];
            case IssueFieldValue::class: //140*
                $code = 1400;
                $id = null;
                if (!is_null($filter)) {
                    $class = get_class($filter);
                    if ($class == Issue::class) {
                        $code = 1405;
                        $id = $filter->getId();
                    } elseif($class == IssueField::class) {
                        $code = 1406;
                        $id = $filter->getId();
                    }
                }
                return (object)['code'=>$code,'id'=>$id];
        }

        return (object)$result;
    }
}