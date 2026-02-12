<?php

namespace App\Tests\Support;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

trait RecreatesDatabaseSchema
{
    /**
     * Пересоздаёт схему БД для всех замапленных Doctrine-сущностей.
     */
    private function recreateDatabaseSchema(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        if ($metadata === []) {
            return;
        }

        try {
            $em->getConnection()->executeStatement('SELECT 1');
        } catch (\Throwable $exception) {
            if ($this->isDatabaseInfrastructureError($exception)) {
                $this->markTestSkipped('Функциональный тест пропущен: база данных недоступна в текущем окружении.');
                return;
            }

            throw $exception;
        }

        $tool = new SchemaTool($em);

        try {
            $tool->dropSchema($metadata);
        } catch (\Throwable $exception) {
            if ($this->isDatabaseInfrastructureError($exception)) {
                $this->markTestSkipped('Функциональный тест пропущен: база данных недоступна в текущем окружении.');
                return;
            }
        }

        try {
            $tool->createSchema($metadata);
        } catch (\Throwable $exception) {
            if ($this->isDatabaseInfrastructureError($exception)) {
                $this->markTestSkipped('Функциональный тест пропущен: база данных недоступна в текущем окружении.');
                return;
            }

            throw $exception;
        }

        $em->clear();
    }

    private function isDatabaseInfrastructureError(\Throwable $exception): bool
    {
        for ($cursor = $exception; $cursor !== null; $cursor = $cursor->getPrevious()) {
            $message = strtolower($cursor->getMessage());
            if (
                str_contains($message, 'could not find driver')
                || str_contains($message, 'connection refused')
                || str_contains($message, 'unknown error while connecting')
                || str_contains($message, 'sqlstate[hy000] [2002]')
                || str_contains($message, 'php_network_getaddresses')
                || str_contains($message, 'no such file or directory')
            ) {
                return true;
            }
        }

        return false;
    }
}
