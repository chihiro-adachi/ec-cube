<?php

namespace Customize\Entity;

use Eccube\Annotation\EntityExtension;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    public function getNaireFeeTotal()
    {
        $total = 0;
        foreach ($this->getOrderItems() as $Item) {
            if ($Item->getOrderItemType()->getId() == 99) {
                $total += $Item->getPriceIncTax() * $Item->getQuantity();
            }
        }

        return $total;
    }
}
