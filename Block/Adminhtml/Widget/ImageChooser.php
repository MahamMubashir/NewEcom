<?php

namespace NewEcom\ShopSmart\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

class ImageChooser extends Template
{
    /**
     * Save image controller path
     */
    protected const UPLOAD_IMAGE_CONTROLLER_PATH = "newecom/widgetimage/uploadimageurl";

    /**
     * @var Factory
     */
    private Factory $elementFactory;

    /**
     * @param Context $context
     * @param Factory $elementFactory
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        UploaderFactory       $uploaderFactory,
        Filesystem            $filesystem,
        StoreManagerInterface $storeManager,
        array   $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $value = $element->getValue();
        $input = $this->elementFactory->create("file", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-file admin__control-file");

        $inputHtml = $input->getElementHtml();
        $element->setData('after_element_html', $inputHtml);
        $element->setValue(null);

        $html = $inputHtml;
        $html .= '<script>
    require(["jquery"], function($){
        var fileInput = $("#' . $element->getHtmlId() . '");
        var previewImage = $("#' . $element->getHtmlId() . '_preview");
        var removeButton = $("#' . $element->getHtmlId() . '_remove");
        var hiddenInput = $("#' . $element->getHtmlId() . '_hidden");

        function updateFileInputVisibility() {
            if(previewImage.attr("src")) {
                fileInput.hide();
                removeButton.show();
            } else {
                fileInput.show();
                removeButton.hide();
            }
        }

        fileInput.on("change", function(){
            var uploadImageUrl = "' . $this->getUrl(self::UPLOAD_IMAGE_CONTROLLER_PATH) . '";
            var file = this.files[0];
            var formData = new FormData();
            formData.append("image", file);
            formData.append("form_key", $("input[name=\'form_key\']").val());
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImage.attr("src", e.target.result).show();
                updateFileInputVisibility();
            }
            reader.readAsDataURL(file);
            $.ajax({
                url: uploadImageUrl,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    let imageUrl = response.response;
                    hiddenInput.val(imageUrl); // Set the value of hidden input
                },
                error: function (response) {
                    console.log(response);
                }
            });
        });

        removeButton.on("click", function(){
            fileInput.val("");
            previewImage.attr("src", "").hide();
            hiddenInput.val(""); // Clear hidden input value
            updateFileInputVisibility();
        });

        updateFileInputVisibility();
    });
    </script>';
        $html .= '<input type="hidden" id="' .
                $element->getHtmlId() .
                '_hidden" name="' . $element->getName() .
                '" value="' . $value . '" />';

        $html .= '<img id="' .
                $element->getHtmlId() .
                '_preview" src="' . $value .
                '" style="max-width: 100px; max-height: 100px;' .
                ($value ? '' : ' display:none;') . '" />';

        $html .= '<span id="' .
                $element->getHtmlId() .
                '_remove" style="cursor: pointer; color: #000000; margin-left: 10px; position:absolute;' .
                ($value ? '' : ' display:none;') . '">&#10060;</span>';

        $element->setData('after_element_html', $html);
        $element->setValue(null);
        return $html;
    }
}
