<?php

namespace NewEcom\ShopSmart\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Log\Log;

class ProductDelete implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     *  Apply product save operation
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $isDeleted = $product->isDeleted();
        if ($isDeleted) {
            $productData[] = $product->getSku();
            $data = [
                "userId" => $this->helper->getShopSmartUserId(),
                "catalog" => $productData
            ];
            $endpoint = "api/catalog/delete";
            $response = $this->helper->sendApiRequest($endpoint, "POST", true, json_encode($data));
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['response']['status'])
                && $responseData['response']['status'] == 'success') {
                Log::Info("The following product has been deleted: " . $product->getId());
            }
        }
    }
}
