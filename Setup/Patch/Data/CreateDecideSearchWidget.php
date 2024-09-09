<?php

namespace NewEcom\ShopSmart\Setup\Patch\Data;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Widget\Model\ResourceModel\Widget\Instance as WidgetInstanceResource;
use Magento\Widget\Model\Widget\InstanceFactory;
use NewEcomAI\ShopSmart\Block\Adminhtml\Widget\DecidePopup;

class CreateDecideSearchWidget implements DataPatchInterface
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
    private State $appState;

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
     * @throws LocalizedException
     */
    public function apply()
    {
        try {
            $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            $this->appState->setAreaCode('frontend');
        }
        $themeId = $this->design->getConfigurationDesignTheme('frontend');
        $widgetInstance = $this->widgetInstanceFactory->create();
        $widgetInstance->setData([
            'instance_type' => DecidePopup::class,
            'theme_id' => $themeId,
            'title' => 'ShopSmart Decide Search Widget',
            'store_ids' => [0, 1],
            'widget_parameters' => '{"shop_smart_decide_popup_position":"new_ecom_popup","shop_smart_decide_layout_product_grid":"4","shop_smart_decide_product_recommendation":"0","shop_smart_decide_heading":"","shop_smart_decide_message_placeholder":"","shop_smart_decide_button_text":"","shop_smart_decide_button_background_color":"#3B0C79","shop_smart_decide_section_button_color":"#FFFFFF","shop_smart_discover_image":"","shop_smart_decide_image_recognition":"1","shop_smart_decide_layout_text":"","shop_smart_decide_layout_description_text":"","shop_smart_decide_example_query_one":"","shop_smart_decide_example_query_two":"","shop_smart_decide_example_query_three":"","shop_smart_decide_example_query_four":"","shop_smart_decide_example_query_five":"","shop_smart_decide_destination_status":"1","shop_smart_decide_custom_css":""}',
            'page_groups' => [
                [
                    'page_group' => 'all_products',
                    'all_products' => [
                        'page_id' => 0,
                        'for' => 'all',
                        'layout_handle' => 'catalog_product_view',
                        'block' => 'product.info.social',
                        'template' => 'NewEcomAI_ShopSmart::widget/decide_template.phtml'
                    ],
                ],
            ],
        ]);
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
