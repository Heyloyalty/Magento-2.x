<?php

namespace Wexo\HeyLoyalty\Api;

interface HeyLoyaltyConfigInterface
{
    /**
     * Check if the HeyLoyal module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get HeyLoyalty API key
     *
     * @return string
     */
    public function getApiKey(): string;

    /**
     * Get HeyLoyalty API secret
     *
     * @return string
     */
    public function getApiSecret(): string;

    /**
     * Check if tracking is activated
     *
     * @return bool
     */
    public function getIsTrackingActivated(): bool;

    /**
     * Get list chosen in config
     *
     * @return string
     */
    public function getList(): string;

    /**
     * Get HeyLoyalty field mapping
     *
     * @return array
     */
    public function getMappings(): array;

    /**
     * Get HeyLoyalty field mapping and map them to customer data
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     * @return array
     */
    public function mapFields(\Magento\Customer\Api\Data\CustomerInterface $customer = null): array;

    /**
     * Get HeyLoyalty tracking id
     *
     * @return string
     */
    public function getTrackingId(): string;

    /**
     * Get HeyLoyalty session time
     *
     * @return string
     */
    public function getSessionTime(): string;

    /**
     * Get if purchase history is activated
     *
     * @return bool
     */
    public function getIsPurchaseHistoryActivated(): bool;

    /**
     * Get purchase history error email
     *
     * @return string
     */
    public function getPurchaseHistoryErrorEmail(): string;
}
