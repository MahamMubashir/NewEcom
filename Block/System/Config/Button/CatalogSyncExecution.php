<?php

namespace NewEcom\ShopSmart\Block\System\Config\Button;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use NewEcom\ShopSmart\Model\Config as ConfigHelper;

class CatalogSyncExecution extends Field
{
    /**
     * @var ConfigHelper
     */
    private ConfigHelper $dataHelper;
    protected const BUTTON_TEMPLATE = 'NewEcom_ShopSmart::system/config/button/catalog_sync_execution.phtml';

    /**
     * @param Context $context
     * @param ConfigHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        context $context,
        ConfigHelper $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get the data helper class
     *
     * @return ConfigHelper
     */
    public function getHelper()
    {
        return $this->dataHelper;
    }

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render button
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $this->addData(
            [
                'button_label' => __('Catalog Sync Now'),
            ]
        );
        return $this->_toHtml();
    }
}
