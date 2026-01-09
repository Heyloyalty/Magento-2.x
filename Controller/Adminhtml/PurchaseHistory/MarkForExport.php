<?php

namespace Wexo\HeyLoyalty\Controller\Adminhtml\PurchaseHistory;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyApiInterface;

class MarkForExport extends Action implements HttpGetActionInterface
{
    public function __construct(
        public Context $context,
        public ResourceConnection $connection,
        public PageFactory $resultPageFactory,
        public HeyLoyaltyApiInterface $api,
        public UrlInterface $url,
        public Emulation $emulation,
        public LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        try {
            $storeId = $this->getRequest()->getParam('store', 1);
            $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
            $securityKey = $this->api->generatePurchaseHistorySecurityKey();
            $url = $this->url->getBaseUrl() . 'wexo_heyloyalty/purchasehistory/csvexport?security_key=' . $securityKey;
            if ($storeId) {
                $url .= "&store_id=$storeId";
            }

            if(str_contains($url, 'localhost')){
                $url = $this->api->generatePurchaseHistory($storeId);
            }
            $this->emulation->stopEnvironmentEmulation();
            $response = $this->api->exportPurchaseHistory($url);
            $this->logger->debug('HeyLoyalty :: Purchase History', [
                'response' => $response
            ]);
            $this->messageManager->addSuccessMessage(__('Orders marked for export successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->error('HeyLoyalty :: Purchase History', [
                'error' => $e->getMessage()
            ]);
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_url->getUrl('adminhtml/system_config/edit', ['section' => 'heyloyalty']));
        return $resultRedirect;
    }
}
