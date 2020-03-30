<?php

namespace A2Global\CRMBundle\Modifier;

use A2Global\CRMBundle\Registry\EntityFieldRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;

class SchemaModifier
{
    private $connection;

    private $entityFieldRegistry;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityFieldRegistry $entityFieldRegistry
    )
    {
        $this->connection = $entityManager->getConnection();
        $this->entityFieldRegistry = $entityFieldRegistry;
    }

    public function createTable($name)
    {
        $this->connection->executeQuery(sprintf('
            CREATE TABLE %s (
                id INT AUTO_INCREMENT NOT NULL,
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4
            COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
            ', self::toTableName($name)));
    }

    public function renameTable($oldName, $newName)
    {
        $this->connection->executeQuery(
            sprintf('RENAME TABLE %s TO %s', self::toTableName($oldName), self::toTableName($newName))
        );
    }

    public function addField($tableName, $fieldName, $fieldType)
    {
        $this->connection->executeQuery(sprintf('
            ALTER TABLE %s ADD %s %s DEFAULT NULL
            ',
            self::toTableName($tableName),
            self::toFieldName($fieldName),
            $this->entityFieldRegistry->find($fieldType)->getMySQLFieldType()
        ));
    }

    public function updateField($tableName, $oldFieldName, $newFieldName, $newFieldType)
    {
        $this->connection->executeQuery(sprintf('
            ALTER TABLE %s CHANGE %s %s %s DEFAULT NULL
            ',
            self::toTableName($tableName),
            self::toFieldName($oldFieldName),
            self::toFieldName($newFieldName),
            $this->entityFieldRegistry->find($newFieldType)->getMySQLFieldType()
        ));
    }

    static protected function toTableName($string): string
    {
        return StringUtility::pluralize(StringUtility::toSnakeCase($string));
    }

    static protected function toFieldName($string): string
    {
        return StringUtility::toSnakeCase($string);
    }
}