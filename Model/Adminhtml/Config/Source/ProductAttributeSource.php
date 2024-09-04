<?php

namespace NewEcom\ShopSmart\Model\Adminhtml\Config\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Get All Product Attribute
 */
class ProductAttributeSource implements OptionSourceInterface
{

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SearchCriteriaBuilder        $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Returns the array
     *
     * @return string[]
     */
    public function toOptionArray()
    {

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $attributeRepository = $this->attributeRepository->getList(
            'catalog_product',
            $searchCriteria
        );

        $productAttribute = [];
        foreach ($attributeRepository->getItems() as $key => $items) {
            $items->getAttributeCode();
            $items->getFrontendLabel();

            $productAttribute[] = ["label" => $items->getFrontendLabel(), "value" => $items->getAttributeCode()];
        }
        return $productAttribute;
    }
}
