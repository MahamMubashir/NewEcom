<?php

namespace NewEcom\ShopSmart\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Config;
use NewEcom\ShopSmart\Model\Log\Log;

class ProductSave implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Data $helper
     * @param Config $config
     */
    public function __construct(
        Data    $helper,
        Config  $config
    ) {
        $this->helper = $helper;
        $this->config = $config;
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
        $isNewProduct = $product->isObjectNew();
        $isProductUpdated = $product->hasDataChanges();
        if ($isNewProduct || $isProductUpdated) {
            $productData[] = $this->helper->getProductAttributeMapping($product->getData());
            $data = [
                "userId" => $this->config->getShopSmartUserId(),
                "catalog" => $productData
            ];
            $endpoint = "api/catalog/update";
            $response = $this->helper->sendApiRequest($endpoint, "POST", true, json_encode($data));
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['response']['status'])
                && $responseData['response']['status'] == 'success') {
                Log::Info($responseData['response']['status']);
            }
        }
    }
}
