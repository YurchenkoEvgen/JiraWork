<?php

namespace App\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TablePrefixEventListener
{
    private string $prefix;

    public function __construct(RequestStack $requestStack)
    {
        $this->prefix = 'pref';
//        $this->prefix = $requestStack->getMainRequest()->getSession()->get('dbprefix')??'';
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName) {
            $classMetadata->setPrimaryTable([
                'name' => $this->getNewTableName($classMetadata->getTableName())
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->getNewTableName($mappedTableName);
            }
        }
    }

    private function getNewTableName(string $tableName):string
    {
        return (empty($this->prefix))?$tableName:$this->prefix.'_'.$tableName;
    }
}