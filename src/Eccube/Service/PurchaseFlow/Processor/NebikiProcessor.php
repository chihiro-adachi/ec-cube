<?php

namespace Eccube\Service\PurchaseFlow\Processor;


use Doctrine\ORM\EntityManagerInterface;
use Eccube\Annotation\ShoppingFlow;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Service\PurchaseFlow\DiscountProcessor;
use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Service\PurchaseFlow\ProcessResult;
use Eccube\Service\PurchaseFlow\PurchaseContext;

/**
 * @ShoppingFlow
 */
class NebikiProcessor implements DiscountProcessor
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * 値引き明細の削除処理を実装します.
     *
     * @param ItemHolderInterface $itemHolder
     * @param PurchaseContext $context
     */
    public function removeDiscountItem(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        foreach ($itemHolder->getItems() as $item) {
            if ($item->getProcessorName() == NebikiProcessor::class) {
                $itemHolder->removeOrderItem($item);
                $this->entityManager->remove($item);
            }
        }
    }

    /**
     * 値引き明細の追加処理を実装します.
     *
     * かならず合計金額等のチェックを行い, 超える場合は利用できる金額まで丸めるか、もしくは明細の追加処理をスキップしてください.
     * 正常に追加できない場合は, ProcessResult::warnを返却してください.
     *
     * @param ItemHolderInterface $itemHolder
     * @param PurchaseContext $context
     *
     * @return ProcessResult|null
     */
    public function addDiscountItem(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        // 10000円以上の場合、値引き明細を追加する.
        if ($itemHolder->getTotal() >= 10000) {

            // 明細種別や税種別を設定
            $DiscountType = $this->entityManager->find(OrderItemType::class, OrderItemType::DISCOUNT);
            $TaxInclude = $this->entityManager->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);
            $Taxation = $this->entityManager->find(TaxType::class, TaxType::NON_TAXABLE);

            // 明細の追加
            $OrderItem = new OrderItem();
            $OrderItem->setProductName($DiscountType->getName())
                ->setPrice(-500)
                ->setQuantity(1)
                ->setTax(0)
                ->setTaxRate(0)
                ->setTaxRuleId(null)
                ->setRoundingType(null)
                ->setOrderItemType($DiscountType)
                ->setTaxDisplayType($TaxInclude)
                ->setTaxType($Taxation)
                ->setOrder($itemHolder)
                ->setProcessorName(NebikiProcessor::class);
            $itemHolder->addItem($OrderItem);
        }
    }
}