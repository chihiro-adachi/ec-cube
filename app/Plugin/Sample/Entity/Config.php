<?php

namespace Plugin\Sample\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="plg_sample_config")
 * @ORM\Entity(repositoryClass="Plugin\Sample\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_data", type="string", length=4000)
     */
    private $sub_data;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSubData()
    {
        return $this->sub_data;
    }

    /**
     * @param string $subData
     *
     * @return $this;
     */
    public function setSubData($subData)
    {
        $this->sub_data = $subData;

        return $this;
    }
}