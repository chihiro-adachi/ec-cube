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

namespace Eccube\Service\PurchaseFlow\Processor;

use Eccube\Entity\BaseInfo;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Order;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Service\PurchaseFlow\PurchaseContext;

/**
 * 送料無料条件を適用する.
 * お届け先ごとに条件判定を行う.
 */
class DeliveryFeeFreeByShippingPreprocessor implements ItemHolderPreprocessor
{
    /**
     * @var BaseInfoRepository
     */
    protected $baseInfoRepository;

    /**
     * DeliveryFeeProcessor constructor.
     *
     * @param BaseInfoRepository $baseInfoRepository
     */
    public function __construct(BaseInfoRepository $baseInfoRepository)
    {
        $this->baseInfoRepository = $baseInfoRepository;
    }

    /**
     * @param ItemHolderInterface $itemHolder
     * @param PurchaseContext $context
     */
    public function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        if (!($this->baseInfoRepository->get()->getDeliveryFreeAmount() || $this->baseInfoRepository->get()->getDeliveryFreeQuantity())) {
            return;
        }

        // Orderの場合はお届け先ごとに判定する.
        if ($itemHolder instanceof Order) {
            /** @var Order $Order */
            $Order = $itemHolder;
            foreach ($Order->getShippings() as $Shipping) {
                $isFree = false;
                $total = 0;
                $quantity = 0;
                foreach ($Shipping->getProductOrderItems() as $Item) {
                    $total += $Item->getPriceIncTax() * $Item->getQuantity();
                    $quantity += $Item->getQuantity();
                }
                // 送料無料（金額）を超えている
                if ($this->baseInfoRepository->get()->getDeliveryFreeAmount()) {
                    if ($total >= $this->baseInfoRepository->get()->getDeliveryFreeAmount()) {
                        $isFree = true;
                    }
                }
                // 送料無料（個数）を超えている
                if ($this->baseInfoRepository->get()->getDeliveryFreeQuantity()) {
                    if ($quantity >= $this->baseInfoRepository->get()->getDeliveryFreeQuantity()) {
                        $isFree = true;
                    }
                }
                if ($isFree) {
                    foreach ($Shipping->getOrderItems() as $Item) {
                        if ($Item->getProcessorName() == DeliveryFeePreprocessor::class) {
                            $Item->setQuantity(0);
                        }
                    }
                }
            }
        }
    }
}
