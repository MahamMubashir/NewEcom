<?php
namespace NewEcom\ShopSmart\Helper;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;
use NewEcom\ShopSmart\Model\Log\Log;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use NewEcom\ShopSmart\Model\Adminhtml\Config\Source\Mode;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Session\Generic;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use NewEcom\ShopSmart\Model\Config as ConfigHelper;

class SyncManagement
{
    /**
     * @var string NewCommAI authentication token
     */
    protected string $token;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfigInterface;
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var CategoryFactory
     */
    private CategoryFactory $categoryFactory;
    /**
     * @var Generic
     */
    private Generic $session;
    /**
     * @var Curl
     */
    private Curl $httpClient;
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $configDataCollectionFactory;

    /**
     * @var State
     */
    private State $state;

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepository
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param Generic $session
     * @param CategoryFactory $categoryFactory
     * @param JsonFactory $resultJsonFactory
     * @param CollectionFactory $configDataCollectionFactory
     * @param Curl $httpClient
     * @param State $state
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        LoggerInterface       $logger,
        StoreManagerInterface $storeManager,
        ProductRepository     $productRepository,
        ScopeConfigInterface  $scopeConfigInterface,
        Generic               $session,
        CategoryFactory       $categoryFactory,
        JsonFactory           $resultJsonFactory,
        CollectionFactory     $configDataCollectionFactory,
        Curl                  $httpClient,
        State                 $state,
        ConfigHelper          $configHelper
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->session = $session;
        $this->httpClient = $httpClient;
        $this->categoryFactory = $categoryFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configDataCollectionFactory = $configDataCollectionFactory;
        $this->state = $state;
        $this->configHelper = $configHelper;
    }
    /**
     * Get Product via sku
     *
     * @param string $sku
     * @return ProductInterface|Product|mixed|null
     * @throws NoSuchEntityException
     */
    public function getProduct($sku)
    {
        if (is_int($sku)) {
            return $this->productRepository->getById($sku);
        }
        return $this->productRepository->get($sku);
    }
    /**
     * Get Category Name
     *
     * @param array $categoryIds
     * @return array|string
     */
    public function getCategoryName(array $categoryIds)
    {
        $categoryNames = [];
        foreach ($categoryIds as $key => $categoryId) {
            $category = $this->categoryFactory->create()->load($categoryId);
            $categoryNames = $category->getName();
        }
        return $categoryNames;
    }
    /**
     * Get Product Attribute Arrays
     *
     * @param object $data
     * @return array
     */
    public function getProductAttributeMapping($data)
    {
        try {
            $productAttributes = $this->configHelper->getShopSmartProductAttribute();
            $attributesArray = explode(',', $productAttributes);
            $valuesString = '';
            $valuesArray = [];
            foreach ($attributesArray as $attribute) {
                if (isset($data[$attribute])) {
                    $valuesString .= $data[$attribute] . '-';
                    $valuesArray[] = $data[$attribute];
                }
            }
            $valuesString = rtrim($valuesString, '-');
            $keyValueString = '';
            foreach ($data as $key => $value) {
                if (in_array($key, $attributesArray)) {
                    $keyValueString .= "$key - $value | ";
                }
            }
            $keyValueString = rtrim($keyValueString, " | ");
            $products = [];
            $products['Id'] = $data['sku'];
            $products['Description'] = $data['description'] . $keyValueString;
            $products['Name'] = $valuesString;
            $categoryIds = $this->productRepository->getById($data['entity_id'])->getCategoryIds();
            $categoryName = $this->getCategoryName($categoryIds) ?? "";
            $products['Tags'] = $valuesArray;
            $products['Price'] = $data['price'] ?? "";
            $products['ProductType'] = $data['type_id'] ?? "";
            $products['Category'] = $categoryName ?? "";
            $products['Vendor'] = "magento";
            return $products;
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
    /**
     * Get Token.
     *
     * Returns an existing token or refreshes the token and returns the new token
     *
     * @return string
     */
    public function getToken()
    {
        $authToken = "";
        $tokenData = $this->session->getData('NewCommAISession');
        if (isset($tokenData['token']) && !empty($tokenData['token']) && isset($tokenData['expiresIn'])) {
            // Check if the token has expired
            if (time() < $tokenData['expiresAt']) {
                $authToken = $tokenData['token'];
            } else {
                // Token has expired, unset the session value
                $this->session->unsetData('NewCommAISession');
            }
        }
        if (empty($authToken)) {
            $accessTokenData = self::getAccessToken();
            if (!empty($accessTokenData['token'])) {
                $authToken = $accessTokenData['token'];
                $expiresAt = time() + $accessTokenData['expiresIn'];
                $this->session->setData('NewCommAISession', [
                    'token' => $authToken,
                    'expiresIn' => $accessTokenData['expiresIn'],
                    'expiresAt' => $expiresAt
                ]);
            }
        }
        return $authToken;
    }
    /**
     * Get the access token
     *
     * @return mixed
     */
    public function getAccessToken()
    {
        $endpoint = "api/oauth/v1/token";
        $postData = json_encode([
            'username' => $this->configHelper->getShopSmartUserName(),
            'password' => $this->configHelper->getShopSmartUserPassword(),
            'userId' => $this->configHelper->getShopSmartUserId()
        ]);
        $response = $this->sendApiRequest($endpoint, "POST", false, $postData);
        return json_decode($response, true);
    }
    /**
     * General function to send Api request
     *
     * @param $endpoint
     * @param $method
     * @param $requireOAuth
     * @param $data
     * @return string
     */
    public function sendApiRequest($endpoint, $method, $requireOAuth, $data = [])
    {
        $mode = $this->configHelper->getShopSmartMode();
        $url = '';
        if ($mode === Mode::STAGING_URL) {
            $url = Mode::STAGING_URL . $endpoint;
        } elseif ($mode === Mode::PRODUCTION_URL) {
            $url = Mode::PRODUCTION_URL . $endpoint;
        }
        $this->httpClient->addHeader("Content-Type", "application/json");
        if ($requireOAuth) {
            $outhToken = $this->getToken();
            $headers = [
                'Authorization' => 'Bearer ' . $outhToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];
            $this->httpClient->setHeaders($headers);
        }
        if (strtoupper($method) === 'POST') {
            $this->httpClient->post($url, $data);
        } elseif (strtoupper($method) === 'GET') {
            $this->httpClient->get($url);
        }
        return $this->httpClient->getBody();
    }

    /**
     * @return string|null
     */
    public function getAreaCode(): ?string
    {
        try {
            return $this->state->getAreaCode();
        } catch (Exception $e) {
            return null;
        }
    }
    /**
     * @param string $code
     *
     * AREA_GLOBAL = 'global';
     * AREA_FRONTEND = 'frontend';
     * AREA_ADMINHTML = 'adminhtml';
     * AREA_DOC = 'doc';
     * AREA_CRONTAB = 'crontab';
     * AREA_WEBAPI_REST = 'webapi_rest';
     * AREA_WEBAPI_SOAP = 'webapi_soap';
     * AREA_GRAPHQL = 'graphql';
     *
     * @return void
     * @throws LocalizedException
     */
    public function setAreaCode(string $code = "global")
    {
        $areaCode = $this->getAreaCode();
        if (!$areaCode) {
            $this->state->setAreaCode($code);
        }
    }
}
