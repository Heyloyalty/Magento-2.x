<?php

namespace Wexo\HeyLoyalty\Block\Adminhtml\System\Config;

use Magento\Framework\View\Element\Html\Select;

abstract class AbstractSelect extends Select
{
    /**
     * Set input name
     *
     * @param string $value
     * @return mixed
     */
    public function setInputName(string $value): mixed
    {
        return $this->setName($value);
    }

    /**
     * Set input id
     *
     * @param string $value
     * @return AbstractSelect
     */
    public function setInputId(string $value): AbstractSelect
    {
        return $this->setId($value);
    }

    /**
     * Convert to HTML
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Get source options
     */
    public function getSourceOptions(): array
    {
        return [];
    }

    /**
     * Get extra parameters
     */
    public function getExtraParams(): string
    {
        return 'style="width:200px"';
    }
}
