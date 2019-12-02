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

namespace Customize\Service\PurchaseFlow\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Annotation\ShoppingFlow;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Entity\OrderItem;
use Eccube\Repository\TaxRuleRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Service\PurchaseFlow\PurchaseContext;

/**
 * @ShoppingFlow
 */
class NaireProcessor implements ItemHolderPreprocessor
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var TaxRuleRepository
     */
    private $taxRuleRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        CartService $cartService,
        EntityManagerInterface $entityManager,
        TaxRuleRepository $taxRuleRepository
    ) {
        $this->cartService = $cartService;
        $this->entityManager = $entityManager;
        $this->taxRuleRepository = $taxRuleRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        $this->removeNaireItems($itemHolder);
        $this->addNaireItems($itemHolder);
    }

    private function addNaireItems(ItemHolderInterface $itemHolder)
    {
        /** @var OrderItem $OrderItem */
        foreach ($itemHolder->getItems() as $item) {
            if ($item->name_printed) {
                $this->addNaireItem($itemHolder, $item);
            }
        }
    }

    private function addNaireItem(ItemHolderInterface $itemHolder, $item)
    {
        $OrderItemType = $this->entityManager
            ->find(OrderItemType::class, 99);
        $TaxInclude = $this->entityManager
            ->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);
        $Taxation = $this->entityManager
            ->find(TaxType::class, TaxType::TAXATION);

        $TaxRule = $this->taxRuleRepository->getByRule();

        $NaireItem = new OrderItem();
        $NaireItem
            ->setPrice(500)
            ->setOrder($itemHolder)
            ->setShipping($item->getShipping())
            ->setQuantity($item->getQuantity())
            ->setOrderItemType($OrderItemType)
            ->setProductName('名入れ：'.$item->name_printed)
            ->setTaxRate($TaxRule->getTaxRate())
            ->setRoundingType($TaxRule->getRoundingType())
            ->setTaxDisplayType($TaxInclude)
            ->setTaxType($Taxation)
            ->setProcessorName(self::class);

        $itemHolder->addItem($NaireItem);
        $item->getShipping()->addOrderItem($NaireItem);
    }

    private function removeNaireItems(ItemHolderInterface $itemHolder)
    {
        foreach ($itemHolder->getItems() as $item) {
            if ($item->getProcessorName() === self::class) {
                $itemHolder->removeOrderItem($item);
                $this->entityManager->remove($item);
            }
        }
    }
}
