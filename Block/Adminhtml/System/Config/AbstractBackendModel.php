<?php

namespace Wexo\HeyLoyalty\Block\Adminhtml\System\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

abstract class AbstractBackendModel extends ConfigValue
{
    /** @var SerializerInterface $serializer */
    protected SerializerInterface $serializer;
    
    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Unset __empty and serialize value before saving
     *
     * @return void
     */
    public function beforeSave(): void
    {
        $value = $this->getValue();
        if (isset($value['__empty'])) {
            unset($value['__empty']);
        }
        $encodedValue = $this->serializer->serialize($value);
        $this->setValue($encodedValue);
    }

    /**
     * Unserialize value before loading
     *
     * @return void
     */
    protected function _afterLoad(): void
    {
        $value = $this->getValue();
        if ($value) {
            $decodedValue = $this->serializer->unserialize($value);
            $this->setValue($decodedValue);
        }
    }
}
