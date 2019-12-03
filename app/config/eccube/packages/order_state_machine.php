<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Eccube\Entity\Master\OrderStatus as Status;
use Eccube\Service\OrderStateMachineContext;

$container->loadFromExtension('framework', [
    'workflows' => [
        'order' => [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'single_state',
                'arguments' => 'status',
            ],
            'supports' => [
                OrderStateMachineContext::class,
            ],
            'initial_place' => (string) Status::NEW,
            'places' => [
                (string) Status::NEW,
                (string) Status::CANCEL,
                (string) Status::IN_PROGRESS,
                (string) Status::DELIVERED,
                (string) Status::PAID,
                (string) Status::PENDING,
                (string) Status::PROCESSING,
                (string) Status::RETURNED,
                // mtb_order_status, mtb_customer_order_status, mtb_order_status_colorに以下を追加しておく.
                '100',  // 名入れ中
                '101',  // 名入れ完了
            ],
            'transitions' => [
                // ステータス遷移の定義を追加.
                // 新規受付→名入れ中→名入れ完了→発送済への一方通行の遷移を作成する.
                'pay' => [
                    'from' => (string) Status::NEW,
                    'to' => (string) Status::PAID,
                ],
                'packing' => [
                    'from' => [(string) Status::NEW, (string) Status::PAID],
                    'to' => (string) Status::IN_PROGRESS,
                ],
                'cancel' => [
                    'from' => [(string) Status::NEW, (string) Status::IN_PROGRESS, (string) Status::PAID],
                    'to' => (string) Status::CANCEL,
                ],
                'back_to_in_progress' => [
                    'from' => (string) Status::CANCEL,
                    'to' => (string) Status::IN_PROGRESS,
                ],
                // 名入れ完了→発送済への遷移.
                'ship' => [
                    'from' => [(string) Status::NEW, (string) Status::PAID, (string) Status::IN_PROGRESS, '101'],
                    'to' => [(string) Status::DELIVERED],
                ],
                'return' => [
                    'from' => (string) Status::DELIVERED,
                    'to' => (string) Status::RETURNED,
                ],
                'cancel_return' => [
                    'from' => (string) Status::RETURNED,
                    'to' => (string) Status::DELIVERED,
                ],
                // 新規→名入れ中への遷移.
                'to_naire' => [
                    'from' => (string) Status::NEW,
                    'to' => '100',
                ],
                // 名入れ中→名入れ完了への遷移.
                'to_naire_complete' => [
                    'from' => '100',
                    'to' => '101',
                ],
            ],
        ],
    ],
]);
