<?php

namespace Wexo\HeyLoyalty\Plugin\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Subscriber;
use Psr\Log\LoggerInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyApiInterface;
use Wexo\HeyLoyalty\Api\HeyLoyaltyConfigInterface;

class SubscriptionManager
{
    public function __construct(
        public LoggerInterface $logger,
        public HeyLoyaltyApiInterface $heyLoyaltyApi,
        public HeyLoyaltyConfigInterface $heyLoyaltyConfig,
        public CustomerRepositoryInterface $customerRepository
    ) {
    }

    public function afterSubscribe(
        \Magento\Newsletter\Model\SubscriptionManager $subject,
        Subscriber $result
    ): Subscriber {
        $this->subscribe($result);
        return $result;
    }

    public function afterUnsubscribe(
        \Magento\Newsletter\Model\SubscriptionManager $subject,
        Subscriber $result
    ): Subscriber {
        $this->unsubscribe($result);
        return $result;
    }

    /**
     * Subscribe customer to newsletter
     *
     * @return Subscriber
     */
    public function afterSubscribeCustomer(
        \Magento\Newsletter\Model\SubscriptionManager $subject,
        Subscriber $result,
        int $customerId,
        int $storeId
    ): Subscriber {
        $this->subscribe($result, $customerId);
        return $result;
    }

    /**
     * Unsubscribe customer from newsletter
     *
     * @return Subscriber
     */
    public function afterUnsubscribeCustomer(
        \Magento\Newsletter\Model\SubscriptionManager $subject,
        Subscriber $result,
        int $customerId,
        int $storeId
    ): Subscriber {
        $this->unsubscribe($result);
        return $result;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function subscribe(Subscriber $subscriber, ?int $customerId = null): void
    {
        $this->logger->info('Customer Subscribed to Newsletter', [
            'subscriber' => $subscriber->getData()
        ]);

        if ($this->heyLoyaltyConfig->isEnabled()) {

            $listId = $this->heyLoyaltyConfig->getList();
            if ($listId !== '' && $listId !== '0') {
                $fields = [
                    'email' => $subscriber->getSubscriberEmail()
                ];
                if ($customerId !== null) {
                    $customer = $this->customerRepository->get($subscriber->getSubscriberEmail());
                    $fields['firstname'] = $customer->getFirstname();
                    $fields['lastname'] = $customer->getLastname();
                    $fields = array_merge(
                        $fields,
                        $this->heyLoyaltyConfig->mapFields($customer)
                    );
                }
                $this->heyLoyaltyApi->createListMember($listId, $fields);
            }
        }
    }

    public function unsubscribe(Subscriber $subscriber): void
    {
        $this->logger->info('Customer Unsubscribed to Newsletter', [
            'subscriber' => $subscriber->getData()
        ]);
        if ($this->heyLoyaltyConfig->isEnabled()) {
            $listId = $this->heyLoyaltyConfig->getList();
            if ($listId !== '' && $listId !== '0') {
                $this->heyLoyaltyApi->deleteListMemberByEmail($listId, $subscriber->getSubscriberEmail());
            }
        }
    }
}
