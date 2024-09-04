<?php

namespace NewEcom\ShopSmart\Controller\ProductInformation;

use Exception;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Log\Log;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

class DecideSearch extends Action
{
    /**
     * Decide Product Information API Endpoint
     */
    protected const DECIDE_API_ENDPOINT = "api/product/decide";

    /**
     * @var Http
     */
    private Http $http;

    /**
     * @var Data
     */
    private Data $dataHelper;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @var AttributeRepository
     */
    protected AttributeRepository $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var StockItemRepository
     */
    protected StockItemRepository $stockItemRepository;

    /**
     * @var Configurable
     */
    private Configurable $configurable;

    /**
     * @var ProductUrl
     */
    private ProductUrl $productUrl;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Context $context
     * @param Http $http
     * @param JsonFactory $resultJsonFactory
     * @param Data $dataHelper
     * @param ProductRepository $productRepository
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param StockItemRepository $stockItemRepository
     * @param Configurable $configurable
     * @param ProductUrl $productUrl
     * @param CheckoutSession $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context                             $context,
        Http                                $http,
        JsonFactory                         $resultJsonFactory,
        Data                                $dataHelper,
        ProductRepository                   $productRepository,
        AttributeRepository                 $attributeRepository,
        SearchCriteriaBuilder               $searchCriteriaBuilder,
        StoreManagerInterface               $storeManager,
        StockItemRepository                 $stockItemRepository,
        Configurable                        $configurable,
        ProductUrl                          $productUrl,
        CheckoutSession                     $checkoutSession,
        LoggerInterface                     $logger
    ) {
        $this->http = $http;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->stockItemRepository = $stockItemRepository;
        $this->configurable = $configurable;
        $this->productUrl = $productUrl;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Decide search controller
     *
     * @return ResponseInterface|Json|ResultInterface|void
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $searchKey = $params['searchKey'];
            $questionId = $params['questionId'];
            $currentProductTitle = $params['currentProductTitle'];
            $currentProductDescription = $params['currentProductDescription'];
            $productRecommendation = $params['productRecommendation'];
            $currentProductId = $params['currentProductId'];
            $userId = $this->dataHelper->getShopSmartUserId();
            if ($this->http->isAjax()) {
                $resultJson = $this->resultJsonFactory->create();
                if (empty($questionId)) {
                    $data = [
                        'userId' => $userId,
                        'listQuestions' => [$searchKey],
                        'currentProduct' => [
                            'title' => $currentProductTitle,
                            'body_html' => $currentProductDescription
                        ]
                    ];
                } else {
                    $data = [
                        'userId' => $userId,
                        "questionId" => $questionId,
                        'listQuestions' => [$searchKey],
                        'currentProduct' => [
                            'title' => $currentProductTitle,
                            'body_html' => $currentProductDescription
                        ]
                    ];
                }
                $endpoint = self::DECIDE_API_ENDPOINT;
                $response = $this->dataHelper->sendApiRequest($endpoint, "POST", true, json_encode($data));
                $responseData = json_decode($response, true);
                $this->checkoutSession->setNewEcomAiDecideSearchClicked($currentProductId);
                $this->checkoutSession->setNewEcomAiDecideQuestionId($responseData['id']);
                if ($responseData['bestProducts']) {
                    foreach ($responseData['bestProducts'] as $product) {
                        $productSku = $product['product']['productId'];
                        $product = $this->productRepository->get($productSku);
                        $productDetails = $this->loadProductDetails($product, $responseData['id'], $productRecommendation);
                        $productInfoArray[] = $productDetails;
                    }
                    return $resultJson->setData(['response' => $responseData, 'products' => $productInfoArray]);
                } else {
                    return $resultJson->setData(['response' => $responseData]);
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Load Product Details method
     *
     * @param $product
     * @param $questionId
     * @param $productRecommendation
     * @return array|void
     */
    public function loadProductDetails($product, $questionId, $productRecommendation)
    {
        try {
            if ($productRecommendation) {
                return [
                    'source' => 'decide',
                    'id' => $product->getId(),
                    'sku' => $product->getSku(),
                    'title' => $product->getName(),
                    'color' => $this->getColorNameByProductId($product),
                    'size' => $this->getSizeByProductId($product),
                    'price' => number_format((float)$product->getData('price'), 2),
                    'imageUrl' => $this->getProductMediaUrl($product),
                    'productUrl' =>  $this->productUrl->getUrl($product),
                    'quantity' => $this->getProductQtyById($product->getId()),
                    'questionId' => $questionId
                ];
            } else {
                return [
                    'title' => $product->getName(),
                    'productUrl' =>  $this->productUrl->getUrl($product)
                ];
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Color Name by Attribute Value
     *
     * @param $product
     * @return array|mixed|string
     */
    public function getColorNameByProductId($product)
    {
        try {
            $attributeCode = 'color'; // Replace with the actual attribute code for color
            if ($product->getTypeId() == 'configurable') {

                $childProducts = $this->configurable->getUsedProducts($product);
                $colorAttribute = $this->attributeRepository->get($attributeCode);
                $colorOptions = $colorAttribute->getSource()->getAllOptions();
                $colorNames = [];
                foreach ($childProducts as $childProduct) {
                    $colorValue = $childProduct->getColor();
                    foreach ($colorOptions as $option) {
                        if ($option['value'] == $colorValue) {
                            $colorNames[] = $option['label'];
                        }
                    }
                }
                return array_unique($colorNames);
            } else {
                $attribute = $this->attributeRepository->get($attributeCode);
                $colorValue = $product->getData($attributeCode);

                if ($attribute && $colorValue !== null) {
                    $options = $attribute->getOptions();
                    foreach ($options as $option) {
                        if ($option['value'] == $colorValue) {
                            return $option['label'];
                        }
                    }
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get size attribute value
     *
     * @param $product
     * @return array
     */
    public function getSizeByProductId($product)
    {
        try {
            $attributeCode = 'size';
            if ($product->getTypeId() == 'configurable') {
                $childProducts = $this->configurable->getUsedProducts($product);
                $sizeAttribute = $this->attributeRepository->get($attributeCode);
                $sizeOptions = $sizeAttribute->getSource()->getAllOptions();

                $sizeNames = [];
                foreach ($childProducts as $childProduct) {
                    $sizeValue = $childProduct->getSize();
                    foreach ($sizeOptions as $option) {
                        if ($option['value'] == $sizeValue) {
                            $sizeNames[] = $option['label'];
                        }
                    }
                }
                return array_unique(array_values($sizeNames));
            } else {
                $attribute = $this->attributeRepository->get($attributeCode);
                $sizeValue = $product->getData($attributeCode);

                if ($attribute && $sizeValue !== null) {
                    $options = $attribute->getOptions();
                    foreach ($options as $option) {
                        if ($option['value'] == $sizeValue) {
                            return $option['label'];
                        }
                    }
                }

            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Option Label Value
     *
     * @param $product
     * @param $attributeCode
     * @return array
     */
    protected function getOptionLabels($product, $attributeCode)
    {
        $attributeOptions = [];
        $attribute = $product->getResource()->getAttribute($attributeCode);
        if ($attribute && $attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option) {
                $value = $option['value'];
                if (!$value) {
                    continue;
                }
                $label = $option['label'];
                $attributeOptions[$value] = $label;
            }
        }
        return $attributeOptions;
    }

    /**
     * Get Product media url
     *
     * @param $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductMediaUrl($product): string
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . 'catalog/product' . $product->getImage();
    }

    /**
     * Get product qty by product Id
     *
     * @param $productId
     * @return float
     * @throws NoSuchEntityException
     */
    public function getProductQtyById($productId)
    {
        $stockItem = $this->stockItemRepository->get($productId);
        return $stockItem->getQty();
    }
}
