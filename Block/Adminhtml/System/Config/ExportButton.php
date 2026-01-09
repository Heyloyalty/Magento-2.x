<?php

namespace Wexo\HeyLoyalty\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Wexo\HeyLoyalty\Api\HeyLoyaltyApiInterface;

class ExportButton extends Field
{
    /** @var string  */
    const string ADMIN_CONTROLLER_URL = "heyloyalty/purchasehistory/markforexport";
    const string FRONTEND_CONTROLLER_URL = "/wexo_heyloyalty/purchasehistory/csvexport";

    /** @var string  */
    public $_template = "Wexo_HeyLoyalty::system/config/export_button.phtml";

    public function __construct(
        public HeyLoyaltyApiInterface $api,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getControllerURL(): string
    {
        $securityKey = $this->api->generatePurchaseHistorySecurityKey();
        $storeId = $this->_request->getParam('store');
        $url = self::FRONTEND_CONTROLLER_URL . "?security_key=$securityKey";
        if($storeId){
            $url .= "&store_id=$storeId";
        }
        return $url;
    }

    public function getAdminControllerURL(): string
    {
        $storeId = $this->_request->getParam('store', false);
        $url = $this->getUrl(self::ADMIN_CONTROLLER_URL);
        if($storeId){
            $url .= "?store=$storeId";
        }
        return $url;
    }

    /**
     * Generate button html
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        $this->addData([
                'id' => 'heyloyalty_export_button',
                'label' => __('Verify Purchase History CSV ( Last 2 years )')
            ]);
        return $this->_toHtml();
    }
}
