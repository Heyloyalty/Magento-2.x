<?php

namespace Wexo\HeyLoyalty\Block\Adminhtml\System\Config\Dropdowns;

use Wexo\HeyLoyalty\Block\Adminhtml\System\Config\AbstractSelect;

class MagentoFields extends AbstractSelect
{
    /**
     * Override to get Magento 2 fields to map to HeyLoyalty fields
     *
     * @return array[]
     */
    #[\Override]
    public function getSourceOptions(): array
    {
        return [
            [
                'label' => 'Store Information',
                'value' => [
                    'store_name' => 'Store Name',
                    'store_phone' => 'Store Phone',
                    'store_country_id' => 'Store Country',
                    'store_region_id' => 'Store Region',
                    'store_postcode' => 'Store Zip/Postal Code',
                    'store_city' => 'Store City',
                    'store_street_line1' => 'Store Street1',
                    'store_street_line2' => 'Store Street2',
                    'store_merchant_vat_number' => 'Store Vat',
                ]
            ],
            [
                'label' => 'Shipping Information',
                'value' => [
                    'shipping_customer_id' => 'Shipping Customer Id',
                    'shipping_firstname' => 'Shipping First Name',
                    'shipping_middlename' => 'Shipping Middle Name',
                    'shipping_lastname' => 'Shipping Last Name',
                    'shipping_email' => 'Shipping Email',
                    'shipping_telephone' => 'Shipping Phone',
                    'shipping_country_id' => 'Shipping Country',
                    'shipping_region' => 'Shipping Region',
                    'shipping_region_id' => 'Shipping Region Id',
                    'shipping_street' => 'Shipping Street',
                    'shipping_postcode' => 'Shipping Zip/Postal Code',
                    'shipping_city' => 'Shipping City',
                    'shipping_company' => 'Shipping Company',
                    'shipping_fax' => 'Shipping Fax',
                    'shipping_vat_id' => 'Shipping VAT',
                ]
            ],
            [
                'label' => 'Billing Information',
                'value' => [
                    'billing_customer_id' => 'Billing Customer Id',
                    'billing_firstname' => 'Billing First Name',
                    'billing_middlename' => 'Billing Middle Name',
                    'billing_lastname' => 'Billing Last Name',
                    'billing_email' => 'Billing Email',
                    'billing_telephone' => 'Billing Phone',
                    'billing_country_id' => 'Billing Country',
                    'billing_region' => 'Billing Region',
                    'billing_region_id' => 'Billing Region Id',
                    'billing_street' => 'Billing Street',
                    'billing_postcode' => 'Billing Zip/Postal Code',
                    'billing_city' => 'Billing City',
                    'billing_company' => 'Billing Company',
                    'billing_fax' => 'Billing Fax',
                    'billing_vat_id' => 'Billing VAT',
                ]
            ]
        ];
    }
}
