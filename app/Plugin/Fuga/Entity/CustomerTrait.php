<?php

namespace Plugin\Fuga\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="nick_name", type="string", length=255)
     */
    private $nick_name;

    /**
     * @return string
     */
    public function getNickName()
    {
        return $this->nick_name;
    }

    /**
     * @param string $nick_name
     */
    public function setNickName($nick_name)
    {
        $this->nick_name = $nick_name;
    }
}