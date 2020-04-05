<?php

namespace A2Global\CRMBundle\Provider;

use Exception;

class CacheDirectoryProvider
{
    const CACHE_SUB_DIRECTORY = 'a2crm';

    protected $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function get(): string
    {
        $tmp = explode('/', $this->cacheDir);
        array_pop($tmp);
        $tmp[] = self::CACHE_SUB_DIRECTORY;
        $cacheDirectory = implode('/', $tmp);

        if (!is_dir($cacheDirectory)) {
            if (false === @mkdir($cacheDirectory, 0775, true)) {
                throw new Exception('Failed to create cache subdirectory: ' . $cacheDirectory);
            }
        }

        if (!is_writable($cacheDirectory)) {
            throw new Exception('Cache subdirectory is not writeable: ' . $cacheDirectory);
        }

        return $cacheDirectory;
    }
}