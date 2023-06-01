<?php

namespace Wexo\HeyLoyalty\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Wexo\HeyLoyalty\Api\HeyLoyaltyApiInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyClientInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyConfigInterface;

class HeyLoyaltyApi implements HeyLoyaltyApiInterface
{
    public function __construct(
        public HeyLoyaltyConfigInterface $config,
        public HeyLoyaltyClientInterface $client,
        public \Magento\Framework\App\ResourceConnection $connection,
        public \Magento\Framework\App\CacheInterface $cache
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function getLists(): array
    {
        return $this->client->fetchLists();
    }

    public function getList(string $id): array
    {
        return $this->client->fetchList($id);
    }

    public function getIsTrackingActivated(): bool
    {
        return $this->config->getIsTrackingActivated();
    }

    public function getTrackingId(): string
    {
        return $this->config->getTrackingId();
    }

    public function exportPurchaseHistory(
        $csvUrl,
        $fields = [
            'member_email',
            'product_id',
            'variation_type',
            'variation_id',
            'product_name',
            'product_price',
            'product_url',
            'original_price',
            'discount',
            'description',
            'currency',
            'event_type',
            'amount'
        ]
    ) {
        $trackingId = $this->config->getTrackingId();
        $errorEmail = $this->config->getPurchaseHistoryErrorEmail();
        return $this->client->exportPurchaseHistory($this->generatePurchaseHistory(1), $trackingId, $fields, $errorEmail);
    }

    public function generatePurchaseHistorySecurityKey(): string
    {
        return md5("HEYLOYALTY_" . date("Y-m-d"));
    }


    public function generatePurchaseHistory($storeId)
    {
        $cacheKey = "heyloyalty_purchase_history_{$storeId}";
        $cacheData = $this->cache->load($cacheKey);
        if ($cacheData) {
            return $cacheData;
        }
        $connection = $this->connection->getConnection();
        $query = '
select
    so.customer_email as member_email,
    IFNULL(
        (select sku from catalog_product_entity where entity_id = (select product_id from sales_order_item where item_id = soi.parent_item_id)),
        soi.sku
    ) as product_id,
    IF(
        ISNULL(soi.parent_item_id),
        "",
        soi.name
    ) as variation_type,
    IF(
        ISNULL(soi.parent_item_id),
        "",
        soi.sku
    ) as variation_id,
    IFNULL(
        (select name from sales_order_item where item_id=soi.parent_item_id),
        soi.name
    ) as product_name,
    soi.price as product_price,
    "" as product_url,
    soi.original_price as original_price,
    soi.discount_amount as discount,
    soi.description as description,
    so.order_currency_code as currency,
    "bought" as event_type,
    soi.qty_ordered as amount
from sales_order as so
left join sales_order_item as soi on so.entity_id = soi.order_id
where
    soi.product_type != "configurable"
    and
    so.created_at > DATE_SUB(NOW(),INTERVAL 2 YEAR)
    and 
    so.store_id = :store_id
order by so.customer_id;
';

        $bind = [
            'store_id' => $storeId
        ];
        $data = $connection->fetchAll($query, $bind);
        $csv = [];
        if (!empty($data)) {
            $headers = join(',', array_keys($data[0]));
            $csv[] = $headers;
            foreach ($data as $row) {
                $csv[] = join(',', array_values($row));
            }
        }
        $output = join(PHP_EOL, $csv);
        $this->cache->save($output, $cacheKey, ['cms_block'], 3600);
        return $output;
    }

    public function createListMember(string $listId, array $fields = []): array
    {
        return $this->client->createListMember($listId, $fields);
    }

    public function deleteListMemberByEmail(string $listId, string $email): array
    {
        return $this->client->deleteListMemberByEmail($listId, $email);
    }
}
