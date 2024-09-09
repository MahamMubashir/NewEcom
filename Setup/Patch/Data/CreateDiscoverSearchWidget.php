<?php

namespace NewEcom\ShopSmart\Setup\Patch\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Widget\Model\Widget\InstanceFactory;
use Magento\Widget\Model\ResourceModel\Widget\Instance as WidgetInstanceResource;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\State;
use Magento\Framework\View\DesignInterface;
use NewEcomAI\ShopSmart\Block\Adminhtml\Widget\DiscoverPopup;

class CreateDiscoverSearchWidget implements DataPatchInterface
{

    /**
     * @var InstanceFactory
     */
    protected InstanceFactory $widgetInstanceFactory;

    /**
     * @var WidgetInstanceResource
     */
    protected WidgetInstanceResource $widgetInstanceResource;

    /**
     * @var PageFactory
     */
    protected PageFactory $pageFactory;

    /**
     * @var Json
     */
    protected Json $json;
    /**
     * @var State
     */
    protected State $appState;

    /**
     * @var DesignInterface
     */
    protected DesignInterface $design;

    /**
     * @param InstanceFactory $widgetInstanceFactory
     * @param WidgetInstanceResource $widgetInstanceResource
     * @param PageFactory $pageFactory
     * @param State $appState
     * @param DesignInterface $design
     * @param Json $json
     */
    public function __construct(
        InstanceFactory $widgetInstanceFactory,
        WidgetInstanceResource $widgetInstanceResource,
        PageFactory $pageFactory,
        State $appState,
        DesignInterface $design,
        Json $json
    ) {
        $this->widgetInstanceFactory = $widgetInstanceFactory;
        $this->widgetInstanceResource = $widgetInstanceResource;
        $this->pageFactory = $pageFactory;
        $this->appState = $appState;
        $this->design = $design;
        $this->json = $json;
    }

    /**
     * Apply data patch for decide widget
     *
     * @return void
     * @throws \Exception
     */
    public function apply()
    {
        try {
            $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            $this->appState->setAreaCode('frontend');
        }
        $themeId = $this->design->getConfigurationDesignTheme('frontend');
        $page = $this->pageFactory->create()->load('home', 'identifier');
        if ($page->getId()) {
            $widgetInstance = $this->widgetInstanceFactory->create();
            $widgetInstance->setData([
                'instance_type' => DiscoverPopup::class,
                'theme_id' => $themeId,
                'title' => 'ShopSmart Discover Search Widget',
                'store_ids' => [0,1],
                'widget_parameters' => '{"shop_smart_discover_popup_position":"new_ecom_popup","shop_smart_discover_layout_product_grid":"4","shop_smart_discover_heading":"","shop_smart_discover_message_placeholder":"","shop_smart_discover_button_text":"","shop_smart_discover_button_background_color":"#3B0C79","shop_smart_discover_section_button_color":"#FFFFFF","shop_smart_discover_image":"","shop_smart_discover_image_recognition":"1","shop_smart_discover_layout_text":"","shop_smart_discover_layout_description_text":"","shop_smart_discover_example_query_one":"","shop_smart_discover_example_query_two":"","shop_smart_discover_example_query_three":"","shop_smart_discover_example_query_four":"","shop_smart_discover_example_query_five":"","shop_smart_discover_destination_status":"1","shop_smart_discover_custom_css":""}',
                'page_groups' => [
                    [
                        'page_group' => 'pages',
                        'pages' => [
                            'page_id' => 0,
                            'for' => 'all',
                            'layout_handle' => 'cms_index_index',
                            'block' => 'content',
                            'template' => 'NewEcomAI_ShopSmart::widget/discover_template.phtml'
                        ],
                    ],
                ],
            ]);
        }
        $widgetInstance->save();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
