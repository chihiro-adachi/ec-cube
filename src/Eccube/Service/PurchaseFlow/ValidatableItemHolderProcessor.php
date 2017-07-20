<?php

namespace Eccube\Service\PurchaseFlow;

use Eccube\Entity\ItemHolderInterface;

abstract class ValidatableItemHolderProcessor implements ItemHolderProcessor
{
    /**
     * @param ItemHolderInterface $itemHolder
     * @param PurchaseContext $context
     * @return ProcessResult
     */
    public final function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        try {
            $this->validate($itemHolder, $context);

            return ProcessResult::success();
        } catch (ItemValidateException $e) {
            return ProcessResult::error($e->getMessage());
        }
    }

    protected abstract function validate(ItemHolderInterface $itemHolder, PurchaseContext $context);
}
