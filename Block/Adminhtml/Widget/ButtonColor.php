<?php

namespace NewEcom\ShopSmart\Block\Adminhtml\Widget;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Change Popup Button Background Color
 */
class ButtonColor extends Template implements BlockInterface
{
    /**
     * Prepare color picker field
     *
     * @param AbstractElement $element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $defaultColor = "#3B0C79";
        $value = $element->getValue() ?: $defaultColor;
        $element->setData('after_element_html', '
                <input type="text"
                    style="height: 30px; width: 100px; "
                    value="' . $value . '"
                    id="button_color"
                    name="' . $element->getName() . '"
                >
                <script type="text/javascript">
                require(["jquery", "jquery/colorpicker/js/colorpicker"], function ($) {
                    $currentElement = $("#button_color");
                    $currentElement.css("backgroundColor", "'. $value .'");
                    $currentElement.ColorPicker({
                        color: "' . $value . '",
                        onChange: function (hsb, hex, rgb) {
                            $currentElement.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
                </script><style>.ButtonColor {z-index: 10010}</style>');
        $element->setValue(null);
        return $element;
    }
}
