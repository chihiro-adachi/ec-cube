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

use Doctrine\ORM\QueryBuilder;
use Eccube\Doctrine\Query\Queries;
use Eccube\Entity\Order;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Util\StringUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * OrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderRepository extends AbstractRepository
{
    /**
     * @var Queries
     */
    protected $queries;

    /**
     * OrderRepository constructor.
     *
     * @param RegistryInterface $registry
     * @param Queries $queries
     */
    public function __construct(RegistryInterface $registry, Queries $queries)
    {
        parent::__construct($registry, Order::class);
        $this->queries = $queries;
    }

    /**
     * @param int $orderId
     * @param OrderStatus $Status
     */
    public function changeStatus($orderId, \Eccube\Entity\Master\OrderStatus $Status)
    {
        $Order = $this
            ->find($orderId)
            ->setOrderStatus($Status)
        ;

        switch ($Status->getId()) {
            case '5': // 発送済へ
                $Order->setShippingDate(new \DateTime());
                break;
            case '6': // 入金済へ
                $Order->setPaymentDate(new \DateTime());
                break;
        }

        $em = $this->getEntityManager();
        $em->persist($Order);
        $em->flush();
    }

    /**
     * @param array $searchData
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderBySearchData($searchData)
    {
        $qb = $this->createQueryBuilder('o');

        $joinedCustomer = false;

        // order_id_start
        if (isset($searchData['order_id_start']) && StringUtil::isNotBlank($searchData['order_id_start'])) {
            $qb
                ->andWhere('o.id >= :order_id_start')
                ->setParameter('order_id_start', $searchData['order_id_start']);
        }

        // order_id_end
        if (isset($searchData['order_id_end']) && StringUtil::isNotBlank($searchData['order_id_end'])) {
            $qb
                ->andWhere('o.id <= :order_id_end')
                ->setParameter('order_id_end', $searchData['order_id_end']);
        }

        // status
        if (!empty($searchData['status']) && $searchData['status']) {
            $qb
                ->andWhere('o.OrderStatus = :status')
                ->setParameter('status', $searchData['status']);
        }

        // name
        if (isset($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $qb
                ->andWhere('CONCAT(o.name01, o.name02) LIKE :name')
                ->setParameter('name', '%'.$searchData['name'].'%');
        }

        // kana
        if (isset($searchData['kana']) && StringUtil::isNotBlank($searchData['kana'])) {
            $qb
                ->andWhere('CONCAT(o.kana01, o.kana02) LIKE :kana')
                ->setParameter('kana', '%'.$searchData['kana'].'%');
        }

        // email
        if (isset($searchData['email']) && StringUtil::isNotBlank($searchData['email'])) {
            $qb
                ->andWhere('o.email = :email')
                ->setParameter('email', $searchData['email']);
        }

        // tel
        if (isset($searchData['tel01']) && StringUtil::isNotBlank($searchData['tel01'])) {
            $qb
                ->andWhere('o.tel01 = :tel01')
                ->setParameter('tel01', $searchData['tel01']);
        }
        if (isset($searchData['tel02']) && StringUtil::isNotBlank($searchData['tel02'])) {
            $qb
                ->andWhere('o.tel02 = :tel02')
                ->setParameter('tel02', $searchData['tel02']);
        }
        if (isset($searchData['tel03']) && StringUtil::isNotBlank($searchData['tel03'])) {
            $qb
                ->andWhere('o.tel03 = :tel03')
                ->setParameter('tel03', $searchData['tel03']);
        }

        // birth
        if (!empty($searchData['birth_start']) && $searchData['birth_start']) {
            if (!$joinedCustomer) {
                $qb->leftJoin('o.Customer', 'c');
                $joinedCustomer = true;
            }

            $date = $searchData['birth_start'];
            $qb
                ->andWhere('c.birth >= :birth_start')
                ->setParameter('birth_start', $date);
        }
        if (!empty($searchData['birth_end']) && $searchData['birth_end']) {
            if (!$joinedCustomer) {
                $qb->leftJoin('o.Customer', 'c');
                $joinedCustomer = true;
            }

            $date = clone $searchData['birth_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('c.birth < :birth_end')
                ->setParameter('birth_end', $date);
        }

        // sex
        if (!empty($searchData['sex']) && count($searchData['sex']) > 0) {
            if (!$joinedCustomer) {
                $qb->leftJoin('o.Customer', 'c');
                $joinedCustomer = true;
            }

            $sexs = [];
            foreach ($searchData['sex'] as $sex) {
                $sexs[] = $sex->getId();
            }

            $qb
                ->andWhere($qb->expr()->in('c.Sex', ':sexs'))
                ->setParameter('sexs', $sexs);
        }

        // payment
        if (!empty($searchData['payment']) && count($searchData['payment'])) {
            $payments = [];
            foreach ($searchData['payment'] as $payment) {
                $payments[] = $payment->getId();
            }
            $qb
                ->leftJoin('o.Payment', 'p')
                ->andWhere($qb->expr()->in('p.id', ':payments'))
                ->setParameter('payments', $payments);
        }

        // oreder_date
        if (!empty($searchData['order_date_start']) && $searchData['order_date_start']) {
            $date = $searchData['order_date_start'];
            $qb
                ->andWhere('o.create_date >= :order_date_start')
                ->setParameter('order_date_start', $date);
        }
        if (!empty($searchData['order_date_end']) && $searchData['order_date_end']) {
            $date = clone $searchData['order_date_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('o.create_date < :order_date_end')
                ->setParameter('order_date_end', $date);
        }

        // create_date
        if (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start'];
            $qb
                ->andWhere('o.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }
        if (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('o.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // payment_total
        if (isset($searchData['payment_total_start']) && StringUtil::isNotBlank($searchData['payment_total_start'])) {
            $qb
                ->andWhere('o.payment_total >= :payment_total_start')
                ->setParameter('payment_total_start', $searchData['payment_total_start']);
        }
        if (isset($searchData['payment_total_end']) && StringUtil::isNotBlank($searchData['payment_total_end'])) {
            $qb
                ->andWhere('o.payment_total <= :payment_total_end')
                ->setParameter('payment_total_end', $searchData['payment_total_end']);
        }

        // buy_product_name
        if (isset($searchData['buy_product_name']) && StringUtil::isNotBlank($searchData['buy_product_name'])) {
            $qb
                ->leftJoin('o.OrderItems', 'oi')
                ->andWhere('oi.product_name LIKE :buy_product_name')
                ->setParameter('buy_product_name', '%'.$searchData['buy_product_name'].'%');
        }

        // Order By
        $qb->addOrderBy('o.update_date', 'DESC');

        return $this->queries->customize(QueryKey::ORDER_SEARCH, $qb, $searchData);
    }

    /**
     * @param  array        $searchData
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderBySearchDataForAdmin($searchData)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->innerJoin('o.OrderItems', 'oi');

        // order_id_start
        if (isset($searchData['order_id']) && StringUtil::isNotBlank($searchData['order_id'])) {
            $qb
                ->andWhere('o.id = :order_id')
                ->setParameter('order_id', $searchData['order_id']);
        }

        // order_code
        if (isset($searchData['order_code']) && StringUtil::isNotBlank($searchData['order_code'])) {
            $qb
                ->andWhere('o.order_code = :order_code')
                ->setParameter('order_code', $searchData['order_code']);
        }

        // order_id_start
        if (isset($searchData['order_id_start']) && StringUtil::isNotBlank($searchData['order_id_start'])) {
            $qb
                ->andWhere('o.id >= :order_id_start')
                ->setParameter('order_id_start', $searchData['order_id_start']);
        }
        // multi
        if (isset($searchData['multi']) && StringUtil::isNotBlank($searchData['multi'])) {
            $multi = preg_match('/^\d{0,10}$/', $searchData['multi']) ? $searchData['multi'] : null;
            $qb
                ->andWhere('o.id = :multi OR o.name01 LIKE :likemulti OR o.name02 LIKE :likemulti OR '.
                           'o.kana01 LIKE :likemulti OR o.kana02 LIKE :likemulti OR o.company_name LIKE :likemulti OR '.
                           'o.order_code LIKE :likemulti')
                ->setParameter('multi', $multi)
                ->setParameter('likemulti', '%'.$searchData['multi'].'%');
        }

        // order_id_end
        if (isset($searchData['order_id_end']) && StringUtil::isNotBlank($searchData['order_id_end'])) {
            $qb
                ->andWhere('o.id <= :order_id_end')
                ->setParameter('order_id_end', $searchData['order_id_end']);
        }

        // status
        $filterStatus = false;
        if (!empty($searchData['status']) && count($searchData['status'])) {
            $qb
                ->andWhere($qb->expr()->in('o.OrderStatus', ':status'))
                ->setParameter('status', $searchData['status']);
            $filterStatus = true;
        }

        if (!$filterStatus) {
            // 購入処理中は検索対象から除外
            $OrderStatuses = $this->getEntityManager()
                ->getRepository('Eccube\Entity\Master\OrderStatus')
                ->findNotContainsBy(['id' => OrderStatus::PROCESSING]);
            $qb->andWhere($qb->expr()->in('o.OrderStatus', ':status'))
                ->setParameter('status', $OrderStatuses);
        }

        // company_name
        if (isset($searchData['company_name']) && StringUtil::isNotBlank($searchData['company_name'])) {
            $qb
                ->andWhere('o.company_name LIKE :company_name')
                ->setParameter('company_name', '%'.$searchData['company_name'].'%');
        }

        // name
        if (isset($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $qb
                ->andWhere('CONCAT(o.name01, o.name02) LIKE :name')
                ->setParameter('name', '%'.$searchData['name'].'%');
        }

        // kana
        if (isset($searchData['kana']) && StringUtil::isNotBlank($searchData['kana'])) {
            $qb
                ->andWhere('CONCAT(o.kana01, o.kana02) LIKE :kana')
                ->setParameter('kana', '%'.$searchData['kana'].'%');
        }

        // email
        if (isset($searchData['email']) && StringUtil::isNotBlank($searchData['email'])) {
            $qb
                ->andWhere('o.email like :email')
                ->setParameter('email', '%'.$searchData['email'].'%');
        }

        // tel
        if (isset($searchData['tel']) && StringUtil::isNotBlank($searchData['tel'])) {
            $tel = preg_replace('/[^0-9]/ ', '', $searchData['tel']);
            $qb
                ->andWhere('CONCAT(o.tel01, o.tel02, o.tel03) LIKE :tel')
                ->setParameter('tel', '%'.$tel.'%');
        }

        // payment
        if (!empty($searchData['payment']) && count($searchData['payment'])) {
            $payments = [];
            foreach ($searchData['payment'] as $payment) {
                $payments[] = $payment->getId();
            }
            $qb
                ->leftJoin('o.Payment', 'p')
                ->andWhere($qb->expr()->in('p.id', ':payments'))
                ->setParameter('payments', $payments);
        }

        // oreder_date
        if (!empty($searchData['order_date_start']) && $searchData['order_date_start']) {
            $date = $searchData['order_date_start'];
            $qb
                ->andWhere('o.order_date >= :order_date_start')
                ->setParameter('order_date_start', $date);
        }
        if (!empty($searchData['order_date_end']) && $searchData['order_date_end']) {
            $date = clone $searchData['order_date_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('o.order_date < :order_date_end')
                ->setParameter('order_date_end', $date);
        }

        // payment_date
        if (!empty($searchData['payment_date_start']) && $searchData['payment_date_start']) {
            $date = $searchData['payment_date_start'];
            $qb
                ->andWhere('o.payment_date >= :payment_date_start')
                ->setParameter('payment_date_start', $date);
        }
        if (!empty($searchData['payment_date_end']) && $searchData['payment_date_end']) {
            $date = clone $searchData['payment_date_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('o.payment_date < :payment_date_end')
                ->setParameter('payment_date_end', $date);
        }

        // shipping_date
        if (!empty($searchData['shipping_date_start']) && $searchData['shipping_date_start']) {
            $date = $searchData['shipping_date_start'];
            $qb
                ->andWhere('o.shipping_date >= :shipping_date_start')
                ->setParameter('shipping_date_start', $date);
        }
        if (!empty($searchData['shipping_date_end']) && $searchData['shipping_date_end']) {
            $date = clone $searchData['shipping_date_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('o.shipping_date < :shipping_date_end')
                ->setParameter('shipping_date_end', $date);
        }

        // update_date
        if (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start'];
            $qb
                ->andWhere('o.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }
        if (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date = $date
                ->modify('+1 days');
            $qb
                ->andWhere('o.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // payment_total
        if (isset($searchData['payment_total_start']) && StringUtil::isNotBlank($searchData['payment_total_start'])) {
            $qb
                ->andWhere('o.payment_total >= :payment_total_start')
                ->setParameter('payment_total_start', $searchData['payment_total_start']);
        }
        if (isset($searchData['payment_total_end']) && StringUtil::isNotBlank($searchData['payment_total_end'])) {
            $qb
                ->andWhere('o.payment_total <= :payment_total_end')
                ->setParameter('payment_total_end', $searchData['payment_total_end']);
        }

        // buy_product_name
        if (isset($searchData['buy_product_name']) && StringUtil::isNotBlank($searchData['buy_product_name'])) {
            $qb
                ->leftJoin('o.OrderItems', 'oi')
                ->andWhere('oi.product_name LIKE :buy_product_name')
                ->setParameter('buy_product_name', '%'.$searchData['buy_product_name'].'%');
        }

        /**
         * 出荷に対する検索条件
         */
        $shippingConditions = [];

        // 伝票番号
        if (!empty($searchData['tracking_number'])) {
            $shippingConditions[] = 's.tracking_number = :tracking_number';
            $qb
                ->setParameter('tracking_number', $searchData['tracking_number']);
        }
        // お届け希望日/予定日(開始)
        if (!empty($searchData['shipping_delivery_date_start'])) {
            $shippingConditions[] = 's.shipping_delivery_date_start >= :shipping_delivery_date_start';
            $qb
                ->setParameter('shipping_delivery_date_start', $searchData['shipping_delivery_date_start']);
        }
        // お届け希望日/予定日(終了)
        if (!empty($searchData['shipping_delivery_date_end'])) {
            $shippingConditions[] = 's.shipping_delivery_date_end >= :shipping_delivery_date_end';
            $qb
                ->setParameter(
                    'shipping_delivery_date_end',
                    (clone $searchData['shipping_delivery_date_end'])->modify('+1 days'));
        }

        if (!empty($shippingConditions)) {
            $andX = implode(' AND ', $shippingConditions);
            $qb->innerJoin('oi.Shipping', 's', 'WITH', $andX);
        }

        // Order By
        $qb->orderBy('o.order_date', 'DESC');
        $qb->addOrderBy('o.id', 'DESC');

        return $this->queries->customize(QueryKey::ORDER_SEARCH_ADMIN, $qb, $searchData);
    }

    /**
     * @param  \Eccube\Entity\Customer $Customer
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderByCustomer(\Eccube\Entity\Customer $Customer)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.Customer = :Customer')
            ->setParameter('Customer', $Customer);

        // Order By
        $qb->addOrderBy('o.id', 'DESC');

        return $this->queries->customize(QueryKey::ORDER_SEARCH_BY_CUSTOMER, $qb, ['customer' => $Customer]);
    }

    /**
     * 会員の合計購入金額を取得、回数を取得
     *
     * @param  \Eccube\Entity\Customer $Customer
     * @param  array $OrderStatuses
     *
     * @return array
     */
    public function getCustomerCount(\Eccube\Entity\Customer $Customer, array $OrderStatuses)
    {
        $result = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS buy_times, SUM(o.total)  AS buy_total')
            ->where('o.Customer = :Customer')
            ->andWhere('o.OrderStatus in (:OrderStatuses)')
            ->setParameter('Customer', $Customer)
            ->setParameter('OrderStatuses', $OrderStatuses)
            ->groupBy('o.Customer')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * 会員が保持する最新の購入処理中の Order を取得する.
     *
     * @param Customer
     *
     * @return Order
     */
    public function getExistsOrdersByCustomer(\Eccube\Entity\Customer $Customer)
    {
        $qb = $this->createQueryBuilder('o');
        $Order = $qb
            ->select('o')
            ->where('o.Customer = :Customer')
            ->setParameter('Customer', $Customer)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if ($Order && $Order->getOrderStatus()->getId() == OrderStatus::PROCESSING) {
            return $Order;
        }

        return null;
    }
}
