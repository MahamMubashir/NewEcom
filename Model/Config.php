<?php

namespace NewEcom\ShopSmart\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use NewEcom\ShopSmart\Model\Log\Log;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

class Config
{
    protected const MODULE_ENABLE = 'shop_smart/general/account_configuration/enable';
    protected const SHOP_SMART_DISCOVER_WIDGET = 'shop_smart/general_newecomai_widgets/shop_smart_discover_widget';
    protected const SHOP_SMART_DECIDE_WIDGET = 'shop_smart/general_newecomai_widgets/shop_smart_decide_widget';
    protected const SHOP_SMART_MODE = 'shop_smart/general_account_configuration/shop_smart_mode';
    protected const SHOP_SMART_USER_ID = 'shop_smart/general_account_configuration/user_id';
    protected const SHOP_SMART_USER_NAME = 'shop_smart/general_account_configuration/user_name';
    protected const SHOP_SMART_USER_PASSWORD = 'shop_smart/general_account_configuration/user_password';
    protected const SHOP_SMART_AB_TESTING = 'shop_smart/general_account_configuration/ab_testing';
    protected const SHOP_SMART_CATALOG_SYNC_BUTTON = 'shop_smart/general_catalog_sync/catalog_sync_button';
    protected const SHOP_SMART_MAPPING = 'shop_smart/general/product_attribute_mapping/mapping';
    protected const LOCALE_TIME_ZONE = 'general/locale/timezone';
    private StoreManagerInterface $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfigInterface;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $configDataCollectionFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param CollectionFactory $configDataCollectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface  $scopeConfigInterface,
        CollectionFactory     $configDataCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->configDataCollectionFactory = $configDataCollectionFactory;
    }

    /**
     * Get value of Discover widget enable configuration
     *
     * @return mixed|void
     */
    public function isDiscoverWidgetEnabled()
    {
        $value = $this->scopeConfigInterface->getValue(
            self::SHOP_SMART_DISCOVER_WIDGET,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        return $value ?: false;
    }
    /**
     * Get value of Decide widget enable configuration
     *
     * @return false|mixed
     */
    public function isDecideWidgetEnabled()
    {
        $value = $this->scopeConfigInterface->getValue(
            self::SHOP_SMART_DECIDE_WIDGET,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        return $value ?: false;
    }
    /**
     *  Get value of mode configuration
     *
     * @return false|mixed
     */
    public function getShopSmartMode()
    {
        $value = $this->scopeConfigInterface->getValue(
            self::SHOP_SMART_MODE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        return $value ?: false;
    }
    /**
     * Get value of user id configuration
     *
     * @return false|mixed
     */
    public function getShopSmartUserId()
    {
        $value = $this->scopeConfigInterface->getValue(
            self::SHOP_SMART_USER_ID,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        return $value ?: false;
    }
    /**
     * Get value of username configuration
     *
     * @return false|mixed
     */
    public function getShopSmartUserName()
    {
        $value = $this->scopeConfigInterface->getValue(
            self::SHOP_SMART_USER_NAME,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        return $value ?: false;
    }
    /**
     * Get value of password configuration
     *
     * @return false|mixed
     */
    public function getShopSmartUserPassword()
    {
        $value = $this->scopeConfigInterface->getValue(
            self::SHOP_SMART_USER_PASSWORD,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        return $value ?: false;
    }

    /**
     * Get value of product attributes mapping configuration
     *
     * @return false|mixed
     */
    public function getShopSmartProductAttribute()
    {
        $value = $this->scopeConfigInterface->getValue(
            self::SHOP_SMART_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        return $value ?: false;
    }

    /**
     * Get value of catalog sync date configuration
     *
     * @return string
     */
    public function getShopSmartCatalogSyncDate()
    {
        try {
            $collection = $this->configDataCollectionFactory->create()
                ->addFieldToFilter('path', self::SHOP_SMART_CATALOG_SYNC_BUTTON)
                ->setPageSize(1);
            $configData = $collection->getFirstItem();
            if ($configData->getValue()) {
                $timestamp = strtotime($configData->getUpdatedAt());
                date_default_timezone_set($this->scopeConfigInterface->getValue(
                    self::LOCALE_TIME_ZONE,
                    ScopeInterface::SCOPE_STORE
                ));
                return date("Y-m-d H:i:s", $timestamp);
            } else {
                return "0000-00-00 00:00:00";
            }
        } catch (NoSuchEntityException $e) {
            Log::Error($e->getMessage());
            return false;
        }
    }

    /**
     * Set value of mode configuration
     *
     * @return void
     */
    public function setShopSmartCatalogSyncDate()
    {
        try {
            $collection = $this->configDataCollectionFactory->create()
                ->addFieldToFilter('path', self::SHOP_SMART_CATALOG_SYNC_BUTTON)
                ->setPageSize(1);
            $configData = $collection->getFirstItem();
            if ($configData->getValue()) {
                date_default_timezone_set($this->scopeConfigInterface->getValue(
                    self::LOCALE_TIME_ZONE,
                    ScopeInterface::SCOPE_STORE
                ));
                $configData->setUpdatedAt(date("Y-m-d H:i:s"));
                $configData->save();
            }
        } catch (NoSuchEntityException $e) {
            Log::Error($e->getMessage());
        }
    }
}
