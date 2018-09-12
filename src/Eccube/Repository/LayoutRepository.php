<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Repository;

use Eccube\Entity\Layout;
use Eccube\Entity\Master\DeviceType;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * LayoutRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LayoutRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Layout::class);
    }

    /**
     * @param $id
     * @param int $deviceTypeId
     */
    public function get($id, $deviceTypeId = DeviceType::DEVICE_TYPE_PC)
    {
        $qb = $this->createQueryBuilder('l');
        try {
            $Layout = $qb
                ->select(['l', 'bp', 'b'])
                ->leftJoin('l.BlockPositions', 'bp')
                ->leftJoin('bp.Block', 'b')
                ->where('l.id = :id')
                ->andWhere('l.DeviceType = :deviceTypeId')
                ->setParameter('id', $id)
                ->setParameter('deviceTypeId', $deviceTypeId)
                ->getQuery()
                ->getSingleResult();
        } catch (\Exception $e) {
            return new Layout();
        }

        return $Layout;
    }
}
