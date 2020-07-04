<?php

/*
 * This file is part of Symfony Boilerplate.
 *
 * (c) Saif Eddin Gmati
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Suspension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Suspension|null find($id, $lockMode = null, $lockVersion = null)
 * @method Suspension|null findOneBy(array $criteria, array $orderBy = null)
 * @method Suspension[]    findAll()
 * @method Suspension[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class SuspensionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suspension::class);
    }
}
