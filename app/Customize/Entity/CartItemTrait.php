<?php

namespace Customize\Entity;

use Eccube\Annotation\EntityExtension;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @EntityExtension("Eccube\Entity\CartItem")
 */
trait CartItemTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $name_printed;
}
