<?php

namespace NewEcom\ShopSmart\Block\Adminhtml\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Config as ConfigHelper;
use NewEcom\ShopSmart\Model\Config\Source\PopupLayout;

/**
 * Get All NewEcomAI Widget Parameters Class
 */
class DiscoverPopup extends Template implements BlockInterface
{
    protected const DISCOVER_SEARCH_CONTROLLER_PATH = "newecom/recommendations/discoversearch";
    protected const PRODUCT_ADD_TO_CART_PATH = "newecom/recommendations/addtocart";
    protected const PRODUCT_REMOVE_FROM_CART_PATH = "newecom/recommendations/productremovefromcart";
    protected const DISCOVER_UPLOAD_IMAGE_CONTROLLER_PATH = "newecom/recommendations/discoveruploadimage";
    protected const POPUP_CLASS = 'newcomPopup';
    protected const LEFT_SIDE_CLASS = 'newcomLeftSide';
    protected const RIGHT_SIDE_CLASS = 'newcomRightSide';
    protected const PRODUCT_GRID_CLASS = 'newcomProductGrid';
    protected const SHOP_SMART_DISCOVER_POPUP_POSITION = 'shop_smart_discover_popup_position';
    protected const SHOP_SMART_DISCOVER_LAYOUT_PRODUCT_GRID = 'shop_smart_discover_layout_product_grid';
    protected const SHOP_SMART_DISCOVER_HEADING = 'shop_smart_discover_heading';
    protected const SHOP_SMART_DISCOVER_MESSAGE_PLACEHOLDER = 'shop_smart_discover_message_placeholder';
    protected const SHOP_SMART_DISCOVER_BUTTON_TEXT = 'shop_smart_discover_button_text';
    protected const SHOP_SMART_DISCOVER_BUTTON_BACKGROUND_COLOR = 'shop_smart_discover_button_background_color';
    protected const SHOP_SMART_DISCOVER_SECTION_BACKGROUND_COLOR = 'shop_smart_discover_section_button_color';
    protected const SHOP_SMART_DISCOVER_IMAGE = 'shop_smart_discover_image';
    protected const SHOP_SMART_DISCOVER_IMAGE_RECOGNITION = 'shop_smart_discover_image_recognition';
    protected const SHOP_SMART_DISCOVER_LAYOUT_TEXT = 'shop_smart_discover_layout_text';
    protected const SHOP_SMART_DISCOVER_LAYOUT_DESCRIPTION_TEXT = 'shop_smart_discover_layout_description_text';
    protected const SHOP_SMART_DISCOVER_EXAMPLE_QUERY_ONE = 'shop_smart_discover_example_query_one';
    protected const SHOP_SMART_DISCOVER_EXAMPLE_QUERY_TWO = 'shop_smart_discover_example_query_two';
    protected const SHOP_SMART_DISCOVER_EXAMPLE_QUERY_THREE = 'shop_smart_discover_example_query_three';
    protected const SHOP_SMART_DISCOVER_EXAMPLE_QUERY_FOUR = 'shop_smart_discover_example_query_four';
    protected const SHOP_SMART_DISCOVER_EXAMPLE_QUERY_FIVE = 'shop_smart_discover_example_query_five';
    protected const SHOP_SMART_DISCOVER_DESTINATION_STATUS = 'shop_smart_discover_destination_status';
    protected const SHOP_SMART_DISCOVER_CUSTOM_CSS = 'shop_smart_discover_custom_css';

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * Popup Template
     * @var string
     */
    protected $_template = "NewEcom_ShopSmart::widget/discover_template.phtml";

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @param Context $context
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param ConfigHelper $configHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data             $helperData,
        StoreManagerInterface $storeManager,
        ConfigHelper           $configHelper,
        array            $data = []
    ) {
        $this->helperData = $helperData;
        $this->data = $data;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * Get Data Helper functions
     *
     * @return Data
     */
    public function getDatahelper()
    {
        return $this->helperData;
    }

    /**
     * Get Data Helper functions
     *
     * @return ConfigHelper
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /**
     * Get Discover Image Url
     *
     * @return array|mixed|null
     */
    public function getImageUrl()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_IMAGE);
    }

    /**
     * Get Discover layout product grid value
     *
     * @return array|mixed|null
     */
    public function getProductGrid()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_LAYOUT_PRODUCT_GRID);
    }

    /**
     * Get Discover popup position
     *
     * Get Popup Position in widget
     *
     * @return string
     */
    public function getPopupPosition()
    {
        $popupPosition = $this->getData(self::SHOP_SMART_DISCOVER_POPUP_POSITION);
        if ($popupPosition == PopupLayout::POPUP_LEFT_SIDE) {
            $popupPosition = self::LEFT_SIDE_CLASS;
        } elseif ($popupPosition == PopupLayout::POPUP_RIGHT_SIDE) {
            $popupPosition = self::RIGHT_SIDE_CLASS;
        } elseif ($popupPosition == PopupLayout::POPUP_PRODUCT_GRID) {
            $popupPosition = self::PRODUCT_GRID_CLASS;
        } else {
            $popupPosition = self::POPUP_CLASS;
        }
        return $popupPosition;
    }

    /**
     * Get Discover widget heading
     *
     * @return array|mixed|null
     */
    public function getHeading()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_HEADING);
    }

    /**
     * Get Discover Search Url path
     *
     * @return string
     */
    public function getDiscoverSearchUrl()
    {
        return $this->getUrl(self:: DISCOVER_SEARCH_CONTROLLER_PATH);
    }

    /**
     * Get Discover widget Add to cart URL path
     *
     * @return string
     */
    public function productAddToCartUrl()
    {
        return $this->getUrl(self:: PRODUCT_ADD_TO_CART_PATH);
    }

    /**
     * Get Discover widget remove from cart URL path
     *
     * @return string
     */
    public function productRemoveFromCartUrl()
    {
        return $this->getUrl(self:: PRODUCT_REMOVE_FROM_CART_PATH);
    }

    /**
     * Get Discover widget upload image URL path
     *
     * @return string
     */
    public function getDiscoverUploadImage()
    {
        return $this->getUrl(self::DISCOVER_UPLOAD_IMAGE_CONTROLLER_PATH);
    }
    /**
     * Get Discover widget message place holder
     *
     * @return array|mixed|null
     */
    public function getMessagePlaceholder()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_MESSAGE_PLACEHOLDER);
    }

    /**
     * Get Discover widget button text
     *
     * @return array|mixed|null
     */
    public function getButtonText()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_BUTTON_TEXT);
    }

    /**
     * Get Discover widget button background color
     *
     * @return array|mixed|null
     */
    public function getButtonBackgroundColor()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_BUTTON_BACKGROUND_COLOR);
    }

    /**
     * Get Discover widget section background color
     *
     * @return array|mixed|null
     */
    public function getSectionBackgroundColor()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_SECTION_BACKGROUND_COLOR);
    }

    /**
     * Get Discover widget Image Recognition Field enable/disable
     *
     * @return array|mixed|null
     */
    public function getImageRecognition()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_IMAGE_RECOGNITION);
    }

    /**
     * Get Discover widget Layout Text
     *
     * @return array|mixed|null
     */
    public function getLayoutText()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_LAYOUT_TEXT);
    }

    /**
     * Get Discover widget layout description Text
     *
     * @return array|mixed|null
     */
    public function getLayoutDescriptionText()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_LAYOUT_DESCRIPTION_TEXT);
    }

    /**
     * Get Discover widget Example Query 1
     *
     * @return array|mixed|null
     */
    public function getExampleQueryOne()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_EXAMPLE_QUERY_ONE);
    }

    /**
     * Get Discover widget Example Query 2
     *
     * @return array|mixed|null
     */
    public function getExampleQueryTwo()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_EXAMPLE_QUERY_TWO);
    }

    /**
     * Get Discover widget Example Query 3
     *
     * @return array|mixed|null
     */
    public function getExampleQueryThree()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_EXAMPLE_QUERY_THREE);
    }

    /**
     * Get Discover widget Example Query 4
     *
     * @return array|mixed|null
     */
    public function getExampleQueryFour()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_EXAMPLE_QUERY_FOUR);
    }

    /**
     * Get Discover widget Example Query 5
     *
     * @return array|mixed|null
     */
    public function getExampleQueryFive()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_EXAMPLE_QUERY_FIVE);
    }

    /**
     * Get Discover widget destination Status
     *
     * @return array|mixed|null
     */
    public function getDestinaltionStatus()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_DESTINATION_STATUS);
    }

    /**
     * Get Discover widget custom css
     *
     * @return array|mixed|null
     */
    public function getCustomCss()
    {
        return $this->getData(self::SHOP_SMART_DISCOVER_CUSTOM_CSS);
    }
}
