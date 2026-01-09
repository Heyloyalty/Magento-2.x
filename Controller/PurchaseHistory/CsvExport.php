<?php

namespace Wexo\HeyLoyalty\Controller\PurchaseHistory;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyApiInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyConfigInterface;

class CsvExport implements HttpGetActionInterface
{
    public function __construct(
        public ResourceConnection $connection,
        public RequestInterface $request,
        public ResponseFactory $responseFactory,
        public Emulation $emulation,
        public StoreManagerInterface $storeManager,
        public HeyLoyaltyConfigInterface $config,
        public HeyLoyaltyApiInterface $api,
        public CacheInterface $cache
    ) {
    }

    /**
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        $securityKeyParam = $this->request->getParam('security_key');
        $storeId = $this->request->getParam('store_id', 1);
        $debug = (bool) $this->request->getParam('debug', false);

        $this->validate($storeId, $securityKeyParam);

        $csv = $this->api->generatePurchaseHistory($storeId);

        $response = $this->responseFactory->create();
        if($debug){
            $response->setHeader('Content-Type', 'text/plain charset=UTF-8');
        }else{
            $response->setHeader('Content-Type', 'text/csv charset=UTF-8');
            $response->setHeader('Content-Disposition', 'attachment; filename="purchase_history.csv"');
        }
        $response->setBody($csv);
        return $response;
    }

    /**
     * @throws Exception
     */
    public function validate($storeId, $securityKeyParam): void
    {
        $securityKey = $this->api->generatePurchaseHistorySecurityKey();
        if($securityKeyParam !== $securityKey){
            throw new \Exception('Security key is not valid');
        }
        $this->emulation->startEnvironmentEmulation($storeId, 'frontend');
        $enabled = $this->config->isEnabled();
        if(!$enabled){
            throw new \Exception('Module is not enabled');
        }
        $this->emulation->stopEnvironmentEmulation();
    }

}
