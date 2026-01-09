<?php

namespace Wexo\HeyLoyalty\Block\Adminhtml\System\Config\Dropdowns;

use Magento\Framework\View\Element\Context;
use Wexo\HeyLoyalty\Api\HeyLoyaltyApiInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyConfigInterface;
use Wexo\HeyLoyalty\Block\Adminhtml\System\Config\AbstractSelect;

class HeyLoyaltyFields extends AbstractSelect
{
    public function __construct(
        public Context $context,
        public HeyLoyaltyConfigInterface $config,
        public HeyLoyaltyApiInterface $api,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Override to get HeyLoyalty to map Magento 2 fields to
     *
     * @return array[]
     */
    #[\Override]
    public function getSourceOptions(): array
    {
        $fields = [];
        $list = $this->api->getList($this->config->getList());
        if($list === [] || empty($list['fields'])){
            return [
                [
                    'label' => 'Select a List to see fields',
                    'value' => [
                        'Select a List to see fields'
                    ]
                ]
            ];
        }
        foreach ($list['fields'] as $field) {
            $fields[$field['name']] = $field['label'];
        }
        return [
            [
                'label' => 'Fields in list',
                'value' => $fields
            ]
        ];
    }
}
