<?php

namespace A2Global\CRMBundle\Provider;

use A2Global\CRMBundle\Modifier\FileManager;
use A2Global\CRMBundle\Utility\StringUtility;

class EntityInfoProvider
{
    protected $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function getEntityList(): array
    {
        $directory = $this->fileManager->getPath(FileManager::CLASS_TYPE_ENTITY);

        return array_map(function ($item) {
            return StringUtility::normalize(basename(substr($item, 0, -4)));
        }, glob($directory.'/*.php'));
    }
}