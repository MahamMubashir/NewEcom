<?php

namespace NewEcom\ShopSmart\Controller\Recommendations;

use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Log\Log;

class DiscoverUploadImage extends Action
{
    /**
     * Save image path
     */
    protected const UPLOAD_IMAGE_PATH = "NewEcom/ShopSmart/images/";

    /**
     * Discovery Api Endpoint
     */
    protected const DISCOVER_API_ENDPOINT = "api/recommendations/discovery";
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var UploaderFactory
     */
    private UploaderFactory $uploaderFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var Data
     */
    protected Data $dataHelper;

    /**
     * @var Http
     */
    private Http $http;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @var StockItemRepository
     */
    private StockItemRepository $stockItemRepository;

    /**
     * @var Configurable
     */
    private Configurable $configurable;

    /**
     * @var ProductUrl
     */
    private ProductUrl $productUrl;

    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @param Context $context
     * @param Http $http
     * @param JsonFactory $jsonFactory
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param Data $dataHelper
     * @param ProductRepository $productRepository
     * @param AttributeRepository $attributeRepository
     * @param StoreManagerInterface $storeManager
     * @param StockItemRepository $stockItemRepository
     * @param Configurable $configurable
     * @param ProductUrl $productUrl
     */
    public function __construct(
        Context               $context,
        Http                  $http,
        JsonFactory           $jsonFactory,
        UploaderFactory       $uploaderFactory,
        Filesystem            $filesystem,
        Data                  $dataHelper,
        ProductRepository     $productRepository,
        AttributeRepository   $attributeRepository,
        StoreManagerInterface $storeManager,
        StockItemRepository   $stockItemRepository,
        Configurable          $configurable,
        ProductUrl            $productUrl
    ) {
        $this->http = $http;
        $this->jsonFactory = $jsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->storeManager = $storeManager;
        $this->stockItemRepository = $stockItemRepository;
        $this->configurable = $configurable;
        $this->productUrl = $productUrl;
        parent::__construct($context);
    }

    /**
     * Upload image for discover Controller
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $fileUrl = $params['uploadImageUrl'];
        $searchKey = $params['searchKey'];
        $questionId = $params['questionId'];
        $contextId = $params['contextId'] ?? "";
        $userId = $this->dataHelper->getShopSmartUserId();
        try {
            if ($this->http->isAjax()) {
                $resultJson = $this->jsonFactory->create();
                if (empty($questionId)) {
                    $data = [
                        "userId" => $userId,
                        "listQuestions" => [$searchKey],
                        "imageUrl" => $fileUrl
                    ];
                } elseif (!empty($contextId)) {
                    $data = [
                        "userId" => $userId,
                        "contextId" => $contextId,
                        "listQuestions" => [$searchKey],
                        "imageUrl" => $fileUrl
                    ];
                } else {
                    $data = [
                        "userId" => $userId,
                        "questionId" => $questionId,
                        "listQuestions" => [$searchKey],
                        "imageUrl" => $fileUrl
                    ];
                }
                $endpoint = self::DISCOVER_API_ENDPOINT;
                $response = $this->dataHelper->sendApiRequest($endpoint, "POST", true, json_encode($data));
                $responseData = json_decode($response, true);
                if ($responseData['bestProducts']) {
                    foreach ($responseData['bestProducts'] as $product) {
                        $productSku = $product['product']['productId'];
                        $product = $this->productRepository->get($productSku);
                        $productDetails = $this->loadProductDetails($product, $responseData['id']);
                        $productInfoArray[] = $productDetails;
                    }
                    return $resultJson->setData(['response' => $responseData, 'products' => $productInfoArray]);
                } else {
                    return $resultJson->setData(["error" => "No product found",
                        'feedback' => $responseData['feedback']]);
                }
            }
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Load product details
     *
     * @param $product
     * @param $questionId
     * @return array
     * @throws NoSuchEntityException
     */
    public function loadProductDetails($product, $questionId)
    {
        return [
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'title' => $product->getName(),
            'color' => $this->getColorNameByProductId($product),
            'size' => $this->getSizeByProductId($product),
            'price' => number_format((float)$product->getData('price'), 2),
            'imageUrl' => $this->getProductMediaUrl($product),
            'productUrl' => $this->productUrl->getUrl($product),
            'quantity' => $this->getProductQtyById($product->getId()),
            'questionId' => $questionId
        ];
    }

    /**
     * Get color name by product id
     *
     * @param $product
     * @return array|mixed|string
     * @throws NoSuchEntityException
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
        } catch (\Exception $e) {
            Log::Error($e->getMessage());
        }
    }

    /**
     * Get size by product id
     *
     * @param $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSizeByProductId($product)
    {
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
    }

    /**
     * Get product media url
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
     * Get product qty by product id
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
