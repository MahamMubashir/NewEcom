<?php

namespace NewEcom\ShopSmart\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Log\Log;
use NewEcom\ShopSmart\Model\Config as ConfigHelper;

class ProductDelete implements ObserverInterface
{
    /**
     * @var \NewEcom\ShopSmart\Helper\SyncManagement
     */
    protected $helper;

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @param Data $helper
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Data $helper,
        ConfigHelper    $configHelper
    ) {
        $this->helper = $helper;
        $this->configHelper = $configHelper;
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
                "userId" => $this->configHelper->getShopSmartUserId(),
                "catalog" => $productData
            ];
            $endpoint = "api/catalog/delete";
            $response = $this->helper->sendApiRequest($endpoint, "POST", true, json_encode($data));
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['response']['status'])
                && $responseData['response']['status'] == 'success') {
                \NewEcom\ShopSmart\Model\Log\Log::Info("The following product has been deleted: " . $product->getId());
            }
        }
    }
}
