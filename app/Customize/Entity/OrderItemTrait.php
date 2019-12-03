<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\OrderItem")
 */
trait OrderItemTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name_printed;

    public function getNamePrinted()
    {
        return $this->name_printed;
    }

    public function setNamePrinted($name_printed = null)
    {
        $this->name_printed = $name_printed;
    }
}