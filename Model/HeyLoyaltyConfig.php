<?php

namespace Wexo\HeyLoyalty\Model;

use InvalidArgumentException;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyConfigInterface;

class HeyLoyaltyConfig implements HeyLoyaltyConfigInterface
{
    public const string CONFIG_ENABLED = 'heyloyalty/general/enabled';
    public const string CONFIG_API_KEY = 'heyloyalty/general/api_key';
    public const string CONFIG_API_SECRET = 'heyloyalty/general/api_secret';
    public const string CONFIG_LIST = 'heyloyalty/general/list';
    public const string CONFIG_MAPPER = 'heyloyalty/general/mappings';
    public const string CONFIG_TRACKING_ACTIVATE = 'heyloyalty/tracking/enabled';
    public const string CONFIG_TRACKING_ID = 'heyloyalty/tracking/id';
    public const string CONFIG_SESSION_TIME = 'heyloyalty/tracking/session_time';
    public const string CONFIG_PURCHASE_HISTORY_ACTIVATE = 'heyloyalty/purchase_history/activate';
    public const string CONFIG_PURCHASE_HISTORY_ERROR_EMAIL = 'heyloyalty/purchase_history/error_email';

    public function __construct(
        public Json $json,
        public Information $storeInformation,
        public StoreManagerInterface $storeManager,
        public ScopeConfigInterface $scopeConfig,
        public AddressRepositoryInterface $addressRepository,
        public LoggerInterface $logger
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_ENABLED,
            ScopeInterface::SCOPE_STORE
        ) === '1';
    }

    public function getApiKey(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_API_KEY,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    public function getApiSecret(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_API_SECRET,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    public function getIsTrackingActivated(): bool
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_TRACKING_ACTIVATE,
            ScopeInterface::SCOPE_STORE
        ) === '1';
    }

    public function getList(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_LIST,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    public function getMappings(): array
    {
        try {
            return $this->json->unserialize($this->scopeConfig->getValue(
                self::CONFIG_MAPPER,
                ScopeInterface::SCOPE_STORE
            )) ?? [];
        } catch (InvalidArgumentException) {
            return [];
        }
    }

    public function mapFields(?CustomerInterface $customer = null): array
    {
        if (!$customer instanceof CustomerInterface) {
            return [];
        }
        $fields = [];
        $mappings = $this->getMappings();
        try {
            $defaultBillingId = $customer->getDefaultBilling();
            $defaultShippingId = $customer->getDefaultShipping();
            $billingAddress = $this->addressRepository->getById($defaultBillingId);
            $shippingAddress = $this->addressRepository->getById($defaultShippingId);
            $storeInformation = $this->storeInformation->getStoreInformationObject($this->storeManager->getStore());

            foreach ($mappings as $mapping) {
                try{
                    if(!isset($mapping['magento_field']) && !isset($mapping['heyloyalty_field'])) {
                        continue;
                    }
                    $magentoField = $mapping['magento_field'];
                    $heyLoyaltyField = $mapping['heyloyalty_field'];
                    switch (true) {
                        case str_contains((string) $magentoField, 'store'):
                            $fields[$heyLoyaltyField] = $storeInformation->getData(str_replace('store_', '', $magentoField));
                            break;
                        case str_contains((string) $magentoField, 'shipping'):
                            $methodName = $this->generateMethodName($magentoField, 'shipping');
                            $fields[$heyLoyaltyField] = $this->getFieldValue($shippingAddress, $magentoField, 'shipping');
                            break;
                        case str_contains((string) $magentoField, 'billing'):
                            $fields[$heyLoyaltyField] = $this->getFieldValue($billingAddress, $magentoField, 'billing');
                            break;
                        default:
                            $fields[$heyLoyaltyField] = $this->getFieldValue($customer, $magentoField);
                            break;
                    }
                }catch(\Throwable $e){
                    $this->logger->debug(
                        'Wexo_HeyLoyaltyConfig::mapFields Error finding map for ' . $magentoField . ' and ' . $heyLoyaltyField,
                        [
                            'customer' => $customer->getEmail(),
                            'mappings' => $mappings,
                            'fields' => $fields,
                            'error' => $e->getMessage()
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                '\Wexo\HeyLoyalty\Model\HeyLoyaltyConfig::mapFields ERROR',
                [
                    'customer' => $customer->getEmail(),
                    'error' => $e->getMessage()
                ]
            );
        }
        $this->logger->debug('\Wexo\HeyLoyalty\Model\HeyLoyaltyConfig::mapFields',
            [
                'customer' => $customer->getEmail(),
                'mappings' => $mappings,
                'fields' => $fields
            ]
        );
        return $fields;
    }

    public function generateMethodName($field, $prefix = ''): string
    {
        $field = str_replace($prefix, '', $field);
        return 'get' . str_replace('_', '', ucwords($field, '_'));
    }

    private function getFieldValue($object, $field, string $prefix = ''): string
    {
        $methodName = $this->generateMethodName($field, $prefix);
        $value = $object->$methodName();
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        return $value;
    }

    public function getTrackingId(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_TRACKING_ID,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    public function getSessionTime(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_SESSION_TIME,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    public function getIsPurchaseHistoryActivated(): bool
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PURCHASE_HISTORY_ACTIVATE,
            ScopeInterface::SCOPE_STORE
        ) === '1';
    }

    public function getPurchaseHistoryErrorEmail(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PURCHASE_HISTORY_ERROR_EMAIL,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }
}
