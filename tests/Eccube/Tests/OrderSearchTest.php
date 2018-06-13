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
use Knp\Component\Pager\PaginatorInterface;

class OrderSearchTest extends EccubeTestCase
{
    public function test_ページネータでLimitOffset()
    {
        $this->createMultipleShippingOrder('あ');
        $this->createMultipleShippingOrder('あ');
        $this->createMultipleShippingOrder('あ');
        $this->createMultipleShippingOrder('あ');
        $this->createMultipleShippingOrder('い');

        /*
         * ページネータで検索する(検索条件なし)
         */
        $this->entityManager->clear();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o, oi, s')
            ->from(Order::class, 'o')
            ->innerJoin('o.OrderItems', 'oi')
            ->innerJoin('oi.Shipping', 's');

        $pagination = $this->container->get(PaginatorInterface::class)
            ->paginate($qb, 1, 3);

        // トータルの件数は5件
        self::assertSame(5, $pagination->getTotalItemCount());
        // 取得した件数は3件
        self::assertCount(3, $pagination);

        foreach ($pagination as $Order) {
            // 明細は3つ, お届け先は２つになってるはず
            self::assertCount(3, $Order->getProductOrderItems());
            self::assertCount(2, $Order->getShippings());
        }

        /*
         * ページネータで検索する(Shippingを検索対象にして検索する)
         */
        $this->entityManager->clear();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o, oi, s')
            ->from(Order::class, 'o')
            ->innerJoin('o.OrderItems', 'oi')
            ->innerJoin('oi.Shipping', 's', 'WITH', 's.note = :note')
            ->setParameter('note', 'あ');

        $pagination = $this->container->get(PaginatorInterface::class)
            ->paginate($qb, 1, 2);

        // トータル件数は4件
        self::assertSame(4, $pagination->getTotalItemCount());
        // 取得した件数は2件
        self::assertCount(2, $pagination);

        foreach ($pagination as $Order) {
            // 明細は1つ, お届け先は1つになってるはず
            self::assertCount(1, $Order->getProductOrderItems());
            self::assertCount(1, $Order->getShippings());
        }

        /*
         * ページネータで検索する(検索条件にヒットしない)
         */
        $this->entityManager->clear();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o, oi, s')
            ->from(Order::class, 'o')
            ->innerJoin('o.OrderItems', 'oi')
            ->innerJoin('oi.Shipping', 's', 'WITH', 's.note = :note')
            ->setParameter('note', 'ん');

        $pagination = $this->container->get(PaginatorInterface::class)
            ->paginate($qb, 1, 2);

        // トータル件数は0件
        self::assertSame(0, $pagination->getTotalItemCount());
        // 取得した件数は0件
        self::assertCount(0, $pagination);
    }

    protected function createDelivery()
    {
        return $this->container->get(Generator::class)->createDelivery();
    }

    protected function createMultipleShippingOrder($searchWord)
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
            ->setNote($searchWord);
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

        return ;
    }
}
