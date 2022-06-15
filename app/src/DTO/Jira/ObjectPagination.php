<?php

namespace App\DTO\Jira;

trait ObjectPagination
{
    public function getArray():array
    {
        if (method_exists(parent::class,'getArray')){
            return parent::getArray();
        } else {
            return [];
        }
    }

    public function isPaginated():bool
    {
        $data = $this->getArray();
        $proprety = [
            'startAt'=>'',
            'maxResults'=>'',
            'total'=>''
        ];
        return empty(array_diff_key($proprety,$data)) && $data['total'] != $data['maxResults'];
    }

    public function isPage():bool
    {
        $data = $this->getArray();
        return array_key_exists('nextPage', $data) && array_key_exists('isLast',$data);
    }

    public function haveNext():bool
    {
        $data = $this->getArray();
        return $this->isPaginated() &&
            ($this->isPage())?!$data['isLast']:$data['startAt']+$data['maxResults']<$data['total'];
    }

    public function getPaginationUrl():string
    {
        return ($this->isPage())?$this->getArray()['nextPage']:'';
    }

    public function getPaginationParams():array
    {
        if ($this->isPaginated()) {
            $data = $this->getArray();
            $result = [
                'startAt'=>min($data['startAt']+$data['maxResults'],$data['total']),
                'maxResults'=>$data['maxResults']
            ];
        }

        return $result??[];
    }
}
