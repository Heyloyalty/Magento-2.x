<?php

namespace Wexo\HeyLoyalty\Api;

use Magento\Customer\Api\Data\CustomerInterface;

interface HeyLoyaltyConfigInterface
{
    /**
     * Check if the HeyLoyal module is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get HeyLoyalty API key
     */
    public function getApiKey(): string;

    /**
     * Get HeyLoyalty API secret
     */
    public function getApiSecret(): string;

    /**
     * Check if tracking is activated
     */
    public function getIsTrackingActivated(): bool;

    /**
     * Get list chosen in config
     */
    public function getList(): string;

    /**
     * Get HeyLoyalty field mapping
     */
    public function getMappings(): array;

    /**
     * Get HeyLoyalty field mapping and map them to customer data
     */
    public function mapFields(?CustomerInterface $customer = null): array;

    /**
     * Get HeyLoyalty tracking id
     */
    public function getTrackingId(): string;

    /**
     * Get HeyLoyalty session time
     */
    public function getSessionTime(): string;

    /**
     * Get if purchase history is activated
     */
    public function getIsPurchaseHistoryActivated(): bool;

    /**
     * Get purchase history error email
     */
    public function getPurchaseHistoryErrorEmail(): string;
}
