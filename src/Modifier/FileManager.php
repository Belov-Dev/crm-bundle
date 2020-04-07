<?php

namespace A2Global\CRMBundle\Modifier;

class FileManager
{
    const CLASS_TYPE_ENTITY = 1;

    const PATH = [
        self::CLASS_TYPE_ENTITY => 'src/Entity'
    ];

    protected $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getPath(int $classType): string
    {
        return sprintf('%s/%s', $this->projectDir, self::PATH[$classType]);
    }

    public function save(int $classType, string $fileName, string $content, string $extension = 'php')
    {
        $filepath = sprintf('%s/%s.%s', $this->getPath($classType), $fileName, $extension);
        file_put_contents($filepath, $content);
        @chmod($filepath, 0664);
    }
}