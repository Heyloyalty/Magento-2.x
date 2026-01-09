<?php

namespace Wexo\HeyLoyalty\Model;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Throwable;
use Wexo\HeyLoyalty\Api\HeyLoyaltyClientInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyConfigInterface;

class HeyLoyaltyClient implements HeyLoyaltyClientInterface
{
    public const string BASE_URI = 'https://api.heyloyalty.com/loyalty/v1/';
    public const string BASE_URI_V2 = 'https://api.heyloyalty.com/loyalty/v2/';
    public const string BI_URI = 'https://bi.heyloyalty.com/api/';
    public const string EXPORT_CSV_URL = 'wexo_heyloyalty/purchasehistory/csvexport';

    public function __construct(
        public Client $client,
        public HeyLoyaltyConfigInterface $config,
        public Json $json,
        public LoggerInterface $logger,
        public StoreManager $storeManager,
        public DirectoryList $dir,
        public Filesystem $filesystem
    ) {
    }

    public function fetchLists(): array
    {
        return $this->vOneRequest("lists");
    }

    public function fetchList(string $listId): array
    {
        return $this->vOneRequest("lists/{$listId}");
    }

    public function createList(
        string $name,
        int $countryId,
        array $fields,
        string $allowDuplicates = 'disallow'
    ): array {
        $payload = [
            'name' => $name,
            'country_id' => $countryId,
            'duplicates' => $allowDuplicates,
            'fields' => $fields
        ];
        return $this->vOneRequest("lists", 'POST', $payload);
    }


    public function editList(
        int $listId,
        string $name,
        int $countryId,
        array $fields,
        string $allowDuplicates = 'disallow'
    ): array {
        $payload = [
            'name' => $name,
            'country_id' => $countryId,
            'duplicates' => $allowDuplicates,
            'fields' => $fields
        ];
        return $this->vOneRequest("lists/{$listId}", 'PUT', $payload);
    }

    public function deleteList(int $listId): array
    {
        return $this->vOneRequest("lists/{$listId}", 'DELETE');
    }


    public function fetchListMembers(
        int $listId,
        int $page = 1,
        int $perPage = 50,
        string $orderBy = 'created_at',
        int $order = 1, // Ascending = 1, Descending = -1
        string $filter = '', // Refer to HeyLoyalty API for filtering
        string $logic = 'and' // Filtering logic to apply with multiple filters ("and"/"or")
    ): array {
        $payload = [
            'page' => $page,
            'perpage' => $perPage,
            'orderby' => $orderBy,
            'order' => $order,
            'logic' => $logic
        ];
        if ($filter !== '') {
            $payload['filter'] = $filter;
        }
        return $this->vOneRequest("lists/{$listId}/members", 'GET', $payload);
    }

    public function fetchListMember(int $listId, string $memberId): array
    {
        return $this->vOneRequest("lists/{$listId}/members/{$memberId}");
    }

    public function createListMember(int $listId, array $params): array
    {
        return $this->vOneRequest("lists/{$listId}/members", 'POST', $params);
    }

    public function editListMember(int $listId, string $memberId, array $params): array
    {
        return $this->vOneRequest("lists/{$listId}/members/{$memberId}", 'PUT', $params);
    }

    public function patchListMember(int $listId, string $memberId, array $params): array
    {
        return $this->vOneRequest("lists/{$listId}/members/{$memberId}", 'PATCH', $params);
    }

    public function deleteListMember(int $listId, string $memberId): array
    {
        return $this->vOneRequest("lists/{$listId}/members/{$memberId}", 'DELETE');
    }

    public function deleteListMemberByEmail(int $listId, string $email): array
    {
        return $this->request(self::BASE_URI_V2, "lists/members/byfield", 'DELETE', [
            "field" => "email",
            "value" => $email,
            "lists" => [$listId]
        ]);
    }


    public function moveListMembers(
        int $sourceListId,
        int $targetListId,
        array $members, // Array of memberId's
        string $action = 'copy', // Can be 'copy' or 'move'
        string $duplicatesField = 'both', // Can be 'email', 'mobile' or 'both'
        string $duplicatesAction = 'patch' // 'patch'=keep existing, 'update'=delete existing, 'skip'=skip duplicates
    ): array {
        $payload = [
            'listId' => $targetListId,
            'handleDuplicates[field]' => $duplicatesField,
            'handleDuplicates[action]' => $duplicatesAction,
            'members' => $members
        ];
        $i = 0;
        foreach ($members as $member) {
            //$payload['members[' . $i . ']'] = $member;
            $i++;
        }
        return $this->vOneRequest("lists/{$sourceListId}/members/bulk/{$action}", 'POST', $payload);
    }

    public function fetchProductfeeds(): array
    {
        return $this->vOneRequest('integrations/productfeed');
    }

    public function createProductfeed(string $name, string $url): array
    {
        $payload = [
            'name' => $name,
            'url' => $url
        ];
        return $this->vOneRequest('integrations/productfeed', 'POST', $payload);
    }

    public function editProductfeed(int $feedId, string $name, string $url): array
    {
        $payload = [
            'name' => $name,
            'url' => $url
        ];
        return $this->vOneRequest("integrations/productfeed/{$feedId}", 'PUT', $payload);
    }

    public function deleteProductfeed(int $feedId): array
    {
        return $this->vOneRequest("integrations/productfeed/{$feedId}", 'DELETE');
    }

    public function fetchProductfeedMapping(int $feedId): array
    {
        return $this->vOneRequest("integrations/productfeed-mapping/{$feedId}");
    }

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
    ): array {
        $payload = [
            'product_feed_id' => $feedId,
            'feed_type' => $feedType,
            'search_field' => $searchField,
            'productId' => $productId,
            'name' => $name
        ];
        $notEmptyArgs = $this->getNotEmptyArguments(func_get_args(), 5);
        foreach ($notEmptyArgs as $key => $arg) {
            $payload[$key] = $arg;
        }
        return $this->vOneRequest('integrations/productfeed-mapping', 'POST', $payload);
    }

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
        string $customField5 = ''
    ): array {
        $payload = [
            'feed_type' => $feedType,
            'search_field' => $searchField,
            'productId' => $productId,
            'name' => $name
        ];
        $notEmptyArgs = $this->getNotEmptyArguments(func_get_args(), 5);
        foreach ($notEmptyArgs as $key => $arg) {
            $payload[$key] = $arg;
        }
        return $this->vOneRequest("integrations/productfeed-mapping/{$feedId}", 'PUT', $payload);
    }

    /**
     * @throws FileSystemException
     * @throws Exception
     */
    public function exportPurchaseHistory(
        string $csvUrl,
        string $trackingId,
        array $fields = [],
        string $sendErrorsTo = '',
        string $dateFormat = 'Y-m-d H:i:s',
        bool $skipHeaderLine = false,
        string $delimiter = ',',
    ): array {

        // if development mode, use the local file instead of the URL
        // Production might have constraints on file permissions, which is why we use a controller url
        // Locally we cannot fetch from .localhost
        if (!str_contains(substr($csvUrl, 0, 10), 'http')) {
            $tmpFile = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
            $content = $csvUrl;
            $tmpFile->writeFile("purchase_history.csv", $content);
            $csvUrl = $tmpFile->getAbsolutePath('purchase_history.csv');
        }

        $payload = [
            [
                'name' => 'file',
                'contents' => \GuzzleHttp\Psr7\Utils::tryfOpen($csvUrl, 'r'),
                'filename' => 'purchase_history.csv',
                'headers' => [
                    'Content-Type' => 'text/csv'
                ]
            ],
            [
                'name' => 'date_format',
                'contents' => $dateFormat
            ],
            [
                'name' => 'skip_header_line',
                'contents' => $skipHeaderLine
            ],
            [
                'name' => 'sendErrorsTo',
                'contents' => $sendErrorsTo
            ],
            [
                'name' => 'delimiter',
                'contents' => $delimiter
            ]
        ];
        foreach ($fields as $field) {
            $payload[] = [
                'name' => 'fields_selected[]',
                'contents' => $field
            ];
        }

        $response = $this->request("https://bi.heyloyalty.com/", "api/transaction/import/{$trackingId}", 'POST', $payload, true);
        if (isset($response['status']) && $response['status'] !== 'error') {
            return $response;
        }
        throw new Exception($response['message']);
    }

    public function vOneRequest(
        string $endpoint,
        string $method = 'GET',
        array $payload = [],
        bool $multipart = false
    ): array {
        return $this->request(self::BASE_URI, $endpoint, $method, $payload, $multipart);
    }

    public function biRequest(
        string $endpoint,
        string $method = 'GET',
        array $payload = [],
        bool $multipart = false
    ): array {
        return $this->request(self::BI_URI, $endpoint, $method, $payload, $multipart);
    }

    public function request(
        string $host,
        string $endpoint,
        string $method = 'GET',
        array $payload = [],
        bool $multipart = false
    ): array {
        $requestTimestamp = gmdate("D, d M Y H:i:s") . ' GMT';
        $requestSignature = base64_encode(hash_hmac('sha256', $requestTimestamp, $this->config->getApiSecret()));
        $headers = [
            'X-Request-Timestamp' => $requestTimestamp,
            'Accept' => 'application/json',
        ];
        if (!$multipart) {
            $headers['Content-Type'] = 'application/json';
        }
        $options = [
            'headers' => $headers,
            'auth' => [
                $this->config->getApiKey(),
                $requestSignature
            ]
        ];

        if ($method !== 'GET' || $payload) {
            $type = $multipart ? 'multipart' : 'json';
            $options[$type] = $payload;
        }
        $message = '';
        try {
            $this->logger->debug('\Wexo\HeyLoyalty\Model\HeyLoyaltyClient::request - Request', [
                'host' => $host,
                'endpoint' => $endpoint,
                'method' => $method,
                'options' => $options
            ]);
            $response = $this->client->request(
                $method,
                $host . $endpoint,
                $options
            );
            $responseJson = $this->json->unserialize($response->getBody()->getContents());

            $this->logger->debug('\Wexo\HeyLoyalty\Model\HeyLoyaltyClient::request - Response', [
                'host' => $host,
                'endpoint' => $endpoint,
                'method' => $method,
                'payload' => $payload,
                'response' => $responseJson
            ]);
            if (!is_array($responseJson)) {
                $responseJson = [
                    'status' => 'success',
                    'message' => $responseJson
                ];
            }
            return $responseJson;
        } catch (ClientException $e) {
            $response = $e?->getResponse();
            $this->logger->error('\Wexo\HeyLoyalty\Model\HeyLoyaltyClient::request Error', [
                'message' => $e->getMessage(),
                'body' => $response?->getBody()?->getContents()
            ]);
            $message = $e->getMessage();
        } catch (Throwable $t) {
            $this->logger->error('\Wexo\HeyLoyalty\Model\HeyLoyaltyClient::request Error', [
                'message' => $t->getMessage()
            ]);
            $message = $t->getMessage();
        }
        return [
            'status' => 'error',
            'message' => $message
        ];
    }

    private function getNotEmptyArguments(array $args, int $shift): array
    {
        $notEmptyArgs = [];
        for ($i = 0; $i < $shift; $i++) {
            array_shift($args);
        }

        foreach ($args as $index => $arg) {
            // Only include arguments that are key-value pairs
            if ($index % 2 === 0 && isset($args[$index + 1])) {
                $key = $arg;
                $value = $args[$index + 1];

                if ($value !== '') {
                    $notEmptyArgs[$key] = $value;
                }
            }
        }
        return $notEmptyArgs;
    }
}
