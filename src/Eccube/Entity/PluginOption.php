<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PluginOption
 */
class PluginOption extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $plugin_code;

    /**
     * @var string
     */
    private $option_key;

    /**
     * @var string
     */
    private $option_value;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set plugin_code
     *
     * @param string $pluginCode
     * @return PluginOption
     */
    public function setPluginCode($pluginCode)
    {
        $this->plugin_code = $pluginCode;

        return $this;
    }

    /**
     * Get plugin_code
     *
     * @return string 
     */
    public function getPluginCode()
    {
        return $this->plugin_code;
    }

    /**
     * Set option_key
     *
     * @param string $optionKey
     * @return PluginOption
     */
    public function setOptionKey($optionKey)
    {
        $this->option_key = $optionKey;

        return $this;
    }

    /**
     * Get option_key
     *
     * @return string 
     */
    public function getOptionKey()
    {
        return $this->option_key;
    }

    /**
     * Set option_value
     *
     * @param string $optionValue
     * @return PluginOption
     */
    public function setOptionValue($optionValue)
    {
        $this->option_value = $optionValue;

        return $this;
    }

    /**
     * Get option_value
     *
     * @return string 
     */
    public function getOptionValue()
    {
        return $this->option_value;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return PluginOption
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return \DateTime 
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date
     *
     * @param \DateTime $updateDate
     * @return PluginOption
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return \DateTime 
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }
}
