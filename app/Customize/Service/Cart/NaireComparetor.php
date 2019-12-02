<?php

namespace Customize\Service\Cart;

use Eccube\Entity\CartItem;
use Eccube\Service\Cart\CartItemComparator;

class NaireComparetor implements CartItemComparator
{
    public function compare(CartItem $Item1, CartItem $Item2)
    {
        $ProductClass1 = $Item1->getProductClass();
        $ProductClass2 = $Item2->getProductClass();
        $product_class_id1 = $ProductClass1 ? (string)$ProductClass1->getId() : null;
        $product_class_id2 = $ProductClass2 ? (string)$ProductClass2->getId() : null;

        return $product_class_id1.$Item1->name_printed === $product_class_id2.$Item2->name_printed;
    }
}
