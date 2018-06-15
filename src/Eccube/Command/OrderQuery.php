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

namespace Eccube\Command;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Order;
use Eccube\Entity\Shipping;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class OrderQuery extends Command
{
    protected static $defaultName = 'eccube:order-query';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(o.id)')
            ->from(Order::class, 'o');

        $order_count = $qb->getQuery()->getSingleScalarResult();

        /**
         * Inner Joinで検索.
         */
        $s = new Stopwatch();
        $s->start('order-test');

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o')
            ->from(Order::class, 'o')
            ->innerJoin('o.OrderItems', 'oi')
            ->innerJoin('oi.Shipping', 's', 'WITH', 's.Pref = :Pref')
            ->setParameter('Pref', 5);

        $pagination = $this->container->get(PaginatorInterface::class)
            ->paginate($qb, 1, 10);

        $this->io->note("->innerJoin('oi.Shipping', 's', 'WITH', 's.Pref = :Pref')");

        $this->io->text('order count : '.$order_count);
        $this->io->text('search count  : '.$pagination->getTotalItemCount());
        $this->io->text('display count : '.count($pagination));

        foreach ($pagination as $order) {
            $this->io->text('  order id -> '.$order->getId());
        }

        $event = $s->stop('order-test');
        $this->io->success('duration: '.$event->getDuration().' ms');

        /**
         * OrderとShippingを Joinする.
         */
        $s = new Stopwatch();
        $s->start('order-test');

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o, s')
            ->from(Order::class, 'o')
            ->innerJoin('o.Shippings', 's')
            ->where('s.Pref = :Pref')
            ->setParameter('Pref', 5)
        ;

        $pagination = $this->container->get(PaginatorInterface::class)
            ->paginate($qb, 1, 10);

        $this->io->note("->innerJoin(Shipping::class, 's', 'WITH', 'o.order_id = s.order_id')");

        $this->io->text('order count : '.$order_count);
        $this->io->text('search count  : '.$pagination->getTotalItemCount());
        $this->io->text('display count : '.count($pagination));

        foreach ($pagination as $order) {
            $this->io->text('  order id -> '.$order->getId());
        }

        $event = $s->stop('order-test');
        $this->io->success('duration: '.$event->getDuration().' ms');
    }
}
