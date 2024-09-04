<?php

namespace NewEcom\ShopSmart\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class QuoteSubmitBefore implements ObserverInterface
{
    /**
     * Observer for product difference b/w add to cart from discover or decide
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $items = $quote->getAllItems();
        foreach ($items as $item) {
            $discoverQuoteAttribute = $item->getData('add_to_cart_from_discover');
            $decideQuoteAttribute = $item->getData('add_to_cart_from_decide');
            if ($item->getData('add_to_cart_from_discover')) {
                $itemId = $item->getData('item_id');
                $orderItems = $order->getItems();
                foreach ($orderItems as $orderItem) {
                    if ($orderItem->getQuoteItemId() == $itemId) {
                        $orderItem->setData('discover_search_product', $discoverQuoteAttribute);
                    }
                }
            }
            if ($item->getData('add_to_cart_from_decide')) {
                $itemId = $item->getData('item_id');
                $orderItems = $order->getItems();
                foreach ($orderItems as $orderItem) {
                    if ($orderItem->getQuoteItemId() == $itemId) {
                        $orderItem->setData('decide_search_product', $decideQuoteAttribute);
                    }
                }
            }
        }
        $order->save();
    }
}
