<?php

namespace Wexo\HeyLoyalty\Api;

interface HeyLoyaltyClientInterface
{
    /**
     * Fetch all lists from HeyLoyalty API
     */
    public function fetchLists(): array;

    /**
     * Fetch a single list from HeyLoyalty API
     */
    public function fetchList(string $listId): array;

    /**
     * Create a list. Refer to HeyLoyalty API for different kinds of fields
     */
    public function createList(
        string $name,
        int $countryId,
        array $fields,
        string $allowDuplicates = 'disallow'
    ): array;

    /**
     * Edit a single list. Overrides all fields; fields not specified as parameter will be deleted
     */
    public function editList(
        int $listId,
        string $name,
        int $countryId,
        array $fields,
        string $allowDuplicates = 'disallow'
    ): array;

    /**
     * Delete a list. Deletes all members on the list and all campaigns and automations sent from that list as well
     */
    public function deleteList(int $listId): array;

    /**
     * Fetch members of a single list from HeyLoyalty API
     */
    public function fetchListMembers(
        int $listId,
        int $page = 1,
        int $perPage = 50,
        string $orderBy = 'created_at',
        int $order = 1, // Ascending = 1, Descending = -1
        string $filter = '', // Refer to HeyLoyalty API for filtering
        string $logic = 'and' // Filtering logic to apply with multiple filters ("and"/"or")
    ): array;

    /**
     * Fetch a single member from a single list from HeyLoyalty API
     */
    public function fetchListMember(int $listId, string $memberId): array;

    /**
     * Create a new member for a list. Get which params to post by calling fetchList() first
     */
    public function createListMember(int $listId, array $params): array;

    /**
     * Edit a member for a list. Get which params to post by calling fetchList() first
     * Overrides all field values not in the params; field values not in params will be deleted
     */
    public function editListMember(int $listId, string $memberId, array $params): array;

    /**
     * Edit a member for a list. Get which params to post by calling fetchList() first
     * Updates only field values sent in params without affecting the others
     */
    public function patchListMember(int $listId, string $memberId, array $params): array;

    /**
     * Deletes a list member
     */
    public function deleteListMember(int $listId, string $memberId): array;

    /**
     * Delete list member by email
     */
    public function deleteListMemberByEmail(int $listId, string $email): array;

    /**
     * Move members to another list
     */
    public function moveListMembers(
        int $sourceListId,
        int $targetListId,
        array $members, // Array of memberId's
        string $action = 'copy', // Can be 'copy' or 'move'
        string $duplicatesField = 'both', // Can be 'email', 'mobile' or 'both'
        string $duplicatesAction = 'patch' // 'patch'=keep existing, 'update'=delete existing, 'skip'=skip duplicates
    ): array;

    /**
     * Fetch product feeds from HeyLoyalty API
     */
    public function fetchProductfeeds(): array;

    /**
     * Add a new product feed
     */
    public function createProductfeed(string $name, string $url): array;

    /**
     * Update a product feed
     */
    public function editProductfeed(int $feedId, string $name, string $url): array;

    /**
     * Delete a product feed
     */
    public function deleteProductfeed(int $feedId): array;

    /**
     * Fetch mapping for a product feed
     */
    public function fetchProductfeedMapping(int $feedId): array;

    /**
     * Create a product feed mapping
     */
    public function createProductfeedMapping(
        int $feedId,
        string $feedType,
        string $searchField,
        int $productId,
        string $name,
        string $url = '',
        string $originalPrice = '',
        string $salePrice = '',
        string $discount = '',
        string $description = '',
        string $imageUrl = '',
        string $currency = '',
        string $categoryName = '',
        string $categoryId = '',
        string $inStock = '',
        string $customField1 = '',
        string $customField2 = '',
        string $customField3 = '',
        string $customField4 = '',
        string $customField5 = '',
    ): array;

    /**
     * Update a product feed mapping
     */
    public function editProductfeedMapping(
        int $feedId,
        string $feedType,
        string $searchField,
        int $productId,
        string $name,
        string $url = '',
        string $originalPrice = '',
        string $salePrice = '',
        string $discount = '',
        string $description = '',
        string $imageUrl = '',
        string $currency = '',
        string $categoryName = '',
        string $categoryId = '',
        string $inStock = '',
        string $customField1 = '',
        string $customField2 = '',
        string $customField3 = '',
        string $customField4 = '',
        string $customField5 = '',
    ): array;


    /**
     * Export purchase history to HeyLoyalty, input is a CSV
     */
    public function exportPurchaseHistory(
        string $csvUrl,
        string $trackingId,
        array $fields = [],
        string $sendErrorsTo = '',
        string $dateFormat = 'Y-m-d H:i:s',
        bool $skipHeaderLine = false,
        string $delimiter = ',',
    ): array;

    /**
     * Send a v1 request.
     */
    public function vOneRequest(
        string $endpoint,
        string $method = 'GET',
        array $payload = [],
        bool $multipart = false
    ): array;

    /**
     * Send a bi request
     */
    public function biRequest(
        string $endpoint,
        string $method = 'GET',
        array $payload = [],
        bool $multipart = false
    ): array;

    /**
     * Send a request to the HeyLoyalty API. Returns [] on all errors.
     */
    public function request(
        string $host,
        string $endpoint,
        string $method = 'GET',
        array $payload = [],
        bool $multipart = false
    ): array;
}
