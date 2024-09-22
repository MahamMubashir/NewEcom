<?php

namespace NewEcom\ShopSmart\Block\Adminhtml\Widget;

use Magento\Catalog\Block\Product\View as ProductViewBlock;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Config as ConfigHelper;
use NewEcom\ShopSmart\Model\Config\Source\PopupLayout;

class DecidePopup extends Template implements BlockInterface
{
    protected const DECIDE_SEARCH_CONTROLLER_PATH = "newecom/productinformation/decidesearch";
    protected const DECIDE_RATE_QUESTION_CONTROLLER_PATH = "newecom/productinformation/ratequestion";
    protected const PRODUCT_ADD_TO_CART_PATH = "newecom/recommendations/addtocart";
    protected const POPUP_CLASS = 'newcomPopup';
    protected const LEFT_SIDE_CLASS = 'newcomLeftSide';
    protected const RIGHT_SIDE_CLASS = 'newcomRightSide';
    protected const PRODUCT_GRID_CLASS = 'newcomProductGrid';
    protected const SHOP_SMART_DECIDE_POPUP_POSITION = 'shop_smart_decide_popup_position';
    protected const SHOP_SMART_DECIDE_PRODUCT_RECOMMENDATION = 'shop_smart_decide_product_recommendation';
    protected const SHOP_SMART_DECIDE_LAYOUT_PRODUCT_GRID = 'shop_smart_decide_layout_product_grid';
    protected const SHOP_SMART_DECIDE_HEADING = 'shop_smart_decide_heading';
    protected const SHOP_SMART_DECIDE_MESSAGE_PLACEHOLDER = 'shop_smart_decide_message_placeholder';
    protected const SHOP_SMART_DECIDE_BUTTON_TEXT = 'shop_smart_decide_button_text';
    protected const SHOP_SMART_DECIDE_BUTTON_BACKGROUND_COLOR = 'shop_smart_decide_button_background_color';
    protected const SHOP_SMART_DECIDE_SECTION_BACKGROUND_COLOR = 'shop_smart_decide_section_button_color';
    protected const SHOP_SMART_DECIDE_IMAGE = 'shop_smart_decide_image';
    protected const SHOP_SMART_DECIDE_IMAGE_RECOGNITION = 'shop_smart_decide_image_recognition';
    protected const SHOP_SMART_DECIDE_LAYOUT_TEXT = 'shop_smart_decide_layout_text';
    protected const SHOP_SMART_DECIDE_LAYOUT_DESCRIPTION_TEXT = 'shop_smart_decide_layout_description_text';
    protected const SHOP_SMART_DECIDE_EXAMPLE_QUERY_ONE = 'shop_smart_decide_example_query_one';
    protected const SHOP_SMART_DECIDE_EXAMPLE_QUERY_TWO = 'shop_smart_decide_example_query_two';
    protected const SHOP_SMART_DECIDE_EXAMPLE_QUERY_THREE = 'shop_smart_decide_example_query_three';
    protected const SHOP_SMART_DECIDE_EXAMPLE_QUERY_FOUR = 'shop_smart_decide_example_query_four';
    protected const SHOP_SMART_DECIDE_EXAMPLE_QUERY_FIVE = 'shop_smart_decide_example_query_five';
    protected const SHOP_SMART_DECIDE_DESTINATION_STATUS = 'shop_smart_decide_destination_status';
    protected const SHOP_SMART_DECIDE_CUSTOM_CSS = 'shop_smart_decide_custom_css';

    /**
     * Decide Template
     * @var string
     */
    protected $_template = "NewEcom_ShopSmart::widget/decide_template.phtml";

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var ProductViewBlock
     */
    protected ProductViewBlock $productViewBlock;

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @param Context $context
     * @param Data $helperData
     * @param ProductViewBlock $productViewBlock
     * @param ConfigHelper $configHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data             $helperData,
        ProductViewBlock $productViewBlock,
        ConfigHelper     $configHelper,
        array            $data = []
    ) {
        $this->productViewBlock = $productViewBlock;
        $this->helperData = $helperData;
        $this->data = $data;
        parent::__construct($context);
        $this->configHelper = $configHelper;
    }

    /**
     * Get Current Product
     *
     * @return Product
     */
    public function getCurrentProduct()
    {
        return $this->productViewBlock->getProduct();
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
     * Get Product Grid Layout Config
     *
     * @return array|mixed|null
     */
    public function getProductGrid()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_LAYOUT_PRODUCT_GRID);
    }

    /**
     * Get Popup Position in widget
     *
     * @return string
     */
    public function getPopupPosition()
    {
        $popupPosition = $this->getData(self::SHOP_SMART_DECIDE_POPUP_POSITION);
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
     * Get Decide Widget Product Recommendation
     *
     * @return array|mixed|null
     */
    public function getProductRecommendation()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_PRODUCT_RECOMMENDATION);
    }

    /**
     * Get Decide Widget Heading
     *
     * @return array|mixed|null
     */
    public function getHeading()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_HEADING);
    }

    /**
     * Get Decide Widget Url Path
     *
     * @return string
     */
    public function getDecideSearchUrl()
    {
        return $this->getUrl(self:: DECIDE_SEARCH_CONTROLLER_PATH);
    }

    /**
     * Get Decide Widget Rate Question Url
     *
     * @return string
     */
    public function getDecideSearchQuestionRateUrl()
    {
        return $this->getUrl(self::DECIDE_RATE_QUESTION_CONTROLLER_PATH);
    }

    /**
     * Get Decide Widget message placeholder field
     *
     * @return array|mixed|null
     */
    public function getMessagePlaceholder()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_MESSAGE_PLACEHOLDER);
    }

    /**
     * GEt decide widget Button text
     *
     * @return array|mixed|null
     */
    public function getButtonText()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_BUTTON_TEXT);
    }

    /**
     * Get Decide Widget button background color
     *
     * @return array|mixed|null
     */
    public function getButtonBackgroundColor()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_BUTTON_BACKGROUND_COLOR);
    }

    /**
     * Get Decide Section Background Color
     *
     * @return array|mixed|null
     */
    public function getSectionBackgroundColor()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_SECTION_BACKGROUND_COLOR);
    }

    /**
     * Get Decide Popup Image
     *
     * @return array|mixed|null
     */
    public function getImageUrl()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_IMAGE);
    }

    /**
     * Get Decide Image Recognition Field enable/disable
     *
     * @return array|mixed|null
     */
    public function getImageRecognition()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_IMAGE_RECOGNITION);
    }

    /**
     * Get Decide Layout Text
     *
     * @return array|mixed|null
     */
    public function getLayoutText()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_LAYOUT_TEXT);
    }

    /**
     * Get Decide Layout Description
     *
     * @return array|mixed|null
     */
    public function getLayoutDescriptionText()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_LAYOUT_DESCRIPTION_TEXT);
    }

    /**
     * Get Decide Example Query 1
     *
     * @return array|mixed|null
     */
    public function getExampleQueryOne()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_EXAMPLE_QUERY_ONE);
    }

    /**
     * Get Decide Example Query 2
     *
     * @return array|mixed|null
     */
    public function getExampleQueryTwo()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_EXAMPLE_QUERY_TWO);
    }

    /**
     * Get Decide Example Query 3
     *
     * @return array|mixed|null
     */
    public function getExampleQueryThree()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_EXAMPLE_QUERY_THREE);
    }

    /**
     * Get Decide Example Query 4
     *
     * @return array|mixed|null
     */
    public function getExampleQueryFour()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_EXAMPLE_QUERY_FOUR);
    }

    /**
     * Get Decide Example Query 5
     *
     * @return array|mixed|null
     */
    public function getExampleQueryFive()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_EXAMPLE_QUERY_FIVE);
    }

    /**
     * Get destination Status
     *
     * @return array|mixed|null
     */
    public function getDestinaltionStatus()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_DESTINATION_STATUS);
    }

    /**
     * Get decide custom css
     *
     * @return array|mixed|null
     */
    public function getCustomCss()
    {
        return $this->getData(self::SHOP_SMART_DECIDE_CUSTOM_CSS);
    }

    /**
     * Get decide widget Add to cart URL path
     *
     * @return string
     */
    public function productAddToCartUrl()
    {
        return $this->getUrl(self:: PRODUCT_ADD_TO_CART_PATH);
    }
}
