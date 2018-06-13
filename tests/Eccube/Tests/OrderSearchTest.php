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

namespace Eccube\Tests;

use Eccube\Entity\Master\ShippingStatus;
use Eccube\Entity\Order;
use Eccube\Entity\Shipping;
use Eccube\Tests\Fixture\Generator;

class OrderSearchTest extends EccubeTestCase
{
    public function test_Shippingを検索対象にする()
    {
        /**
         * 単一出荷の受注を作成する
         */
        $Product = $this->createProduct();
        $Customer = $this->createCustomer();
        $Order = $this->createOrderWithProductClasses($Customer, $Product->getProductClasses()->toArray());

        // 明細は3つ, お届け先は1つ生成される
        self::assertCount(3, $Order->getProductOrderItems());
        self::assertCount(1, $Order->getShippings());

        /**
         * 出荷を作成して, 複数配送の受注にする
         */
        $Shipping = new Shipping();
        $Shipping->copyProperties($Customer);
        $Shipping
            ->setPref($Customer->getPref())
            ->setDelivery($this->createDelivery())
            ->setNote('ここが検索条件です');
        $ShippingStatus = $this->entityManager->find(ShippingStatus::class, ShippingStatus::PREPARED);
        $Shipping->setShippingStatus($ShippingStatus);
        $this->entityManager->persist($Shipping);

        foreach ($Order->getOrderItems() as $Item) {
            if ($Item->isProduct()) {
                // 最初の1件だけお届け先のヒモ付をかえて、複数配送にする
                $Item->setShipping($Shipping);
                $Shipping->addOrderItem($Item);
                break;
            }
        }

        $this->entityManager->flush();

        // 明細は3つ, お届け先は2つになってるはず
        self::assertCount(3, $Order->getProductOrderItems());
        self::assertCount(2, $Order->getShippings());

        /*
         * 条件なしで検索する
         */
        $this->entityManager->clear();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o, oi, s')
            ->from(Order::class, 'o')
            ->innerJoin('o.OrderItems', 'oi')
            ->innerJoin('oi.Shipping', 's');

        $Orders = $qb->getQuery()->getResult();
        self::assertCount(1, $Orders);

        $Order = $Orders[0];
        // 明細は3つ, お届け先は２つになってるはず
        self::assertCount(3, $Order->getProductOrderItems());
        self::assertCount(2, $Order->getShippings());

        /*
         * Shippingを検索対象にして検索する
         */
        $this->entityManager->clear();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o, oi, s')
            ->from(Order::class, 'o')
            ->innerJoin('o.OrderItems', 'oi')
            ->innerJoin('oi.Shipping', 's', 'WITH', 's.note = :note')
            ->setParameter('note', 'ここが検索条件です');

        $Orders = $qb->getQuery()->getResult();
        self::assertCount(1, $Orders);

        $Order = $Orders[0];
        // 明細は1つ, お届け先は1つになってるはず
        self::assertCount(1, $Order->getProductOrderItems());
        self::assertCount(1, $Order->getShippings());

        /*
         * Shippingを検索対象にして検索し、マッチしない場合
         */
        $this->entityManager->clear();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o, oi, s')
            ->from(Order::class, 'o')
            ->innerJoin('o.OrderItems', 'oi')
            ->innerJoin('oi.Shipping', 's', 'WITH', 's.note = :note')
            ->setParameter('note', 'ここが検索条件ですよーーー');

        $Orders = $qb->getQuery()->getResult();
        // 受注はヒットしないはず
        self::assertCount(0, $Orders);
    }

    protected function createDelivery()
    {
        return $this->container->get(Generator::class)->createDelivery();
    }
}
