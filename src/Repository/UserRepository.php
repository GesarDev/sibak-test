<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param list<string> $emails
     * @return array<string,string>
     */
    public function findNamesByEmails(array $emails): array
    {
        if ($emails === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('u')
            ->select('LOWER(u.email) AS email', 'u.name AS name')
            ->where('LOWER(u.email) IN (:emails)')
            ->setParameter('emails', $emails)
            ->getQuery()
            ->getArrayResult();

        $out = [];
        foreach ($rows as $row) {
            $email = (string) ($row['email'] ?? '');
            $name = (string) ($row['name'] ?? '');
            if ($email !== '' && $name !== '') {
                $out[$email] = $name;
            }
        }

        return $out;
    }
}
