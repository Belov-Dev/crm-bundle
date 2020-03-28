<?php

namespace A2Global\CRMBundle\Modifier;

use Doctrine\ORM\EntityManagerInterface;

class SchemaModifier
{
    private $connection;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();

    }

    public function createTable($name)
    {
        $this->connection->executeQuery(sprintf('CREATE TABLE %s (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB', $name));
    }
}