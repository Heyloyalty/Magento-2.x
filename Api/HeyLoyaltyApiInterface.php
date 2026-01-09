<?php

namespace Wexo\HeyLoyalty\Api;

interface HeyLoyaltyApiInterface
{
    /**
     * Get is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get all lists
     */
    public function getLists(): array;

    /**
     * Get a list from client
     */
    public function getList(string $id): array;

    /**
     * Get from config if tracking is activated
     */
    public function getIsTrackingActivated(): bool;

    /**
     * Get tracking id from config
     */
    public function getTrackingId(): string;

    /**
     * Export purchase history
     */
    public function exportPurchaseHistory(string $csvUrl): array;

    /**
     * Generate purchase history
     *
     * @param int|string $storeId
     */
    public function generatePurchaseHistory(mixed $storeId): string;

    /**
     * Generate purchase history security key
     */
    public function generatePurchaseHistorySecurityKey(): string;


    /**
     * Create a list member in Heyloyalty
     */
    public function createListMember(string $listId, array $fields = []): array;

    /**
     * Delete list member by email
     */
    public function deleteListMemberByEmail(string $listId, string $email): array;
}
