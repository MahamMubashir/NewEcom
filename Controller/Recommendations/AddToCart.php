<?php

namespace NewEcom\ShopSmart\Controller\Recommendations;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Add to cart product on discover search
 */
class AddToCart extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var Cart
     */
    protected Cart $cart;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cartRepository;

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $quoteFactory;

    /**
     * @var CartManagementInterface
     */
    protected CartManagementInterface $cartManagement;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;
    private \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeConfigurable;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomerSession $customerSession
     * @param Cart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteFactory $quoteFactory
     * @param CartManagementInterface $cartManagement
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $cartRepository,
        QuoteFactory $quoteFactory,
        CartManagementInterface $cartManagement,
        StoreManagerInterface $storeManager,
        Config $config,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeConfigurable,
        CheckoutSession $checkoutSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
        $this->quoteFactory = $quoteFactory;
        $this->cartManagement = $cartManagement;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
        $this->productTypeConfigurable = $productTypeConfigurable;
    }

    /**
     * Add to cart controller
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = json_decode($this->getRequest()->getContent(), true);

        if (!isset($data['productId']) || !isset($data['colorOption']) || !isset($data['sizeOption'])) {
            return $resultJson->setData(['error' => true, 'message' => __('Invalid data.')]);
        }

        $productId = $data['productId'];
        $questionId = $data['questionId'];
        $source = $data['source'];
        $colorOptionLabel = $data['colorOption']['value'];
        $sizeOptionLabel = $data['sizeOption']['value'];
        $qty = isset($data['qty']) ? (int)$data['qty'] : 1;

        try {
            // Fetch the configurable product
            $requestProduct = $this->productRepository->get($productId);

            $colorAttribute = $this->config->getAttribute('catalog_product', 'color');
            $sizeAttribute = $this->config->getAttribute('catalog_product', 'size');

            $colorOptions = $colorAttribute->getSource()->getAllOptions();
            $sizeOptions = $sizeAttribute->getSource()->getAllOptions();

            // Map option labels to option values
            $colorOptionsMap = array_column($colorOptions, 'value', 'label');
            $sizeOptionsMap = array_column($sizeOptions, 'value', 'label');

            $colorOptionValue = $colorOptionsMap[$colorOptionLabel] ?? null;
            $sizeOptionValue = $sizeOptionsMap[$sizeOptionLabel] ?? null;

            if ($colorOptionValue === null || $sizeOptionValue === null) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid product options.'));
            }

            // Prepare the super_attribute array
            $superAttribute = [
                $colorAttribute->getAttributeId() => $colorOptionValue,
                $sizeAttribute->getAttributeId() => $sizeOptionValue,
            ];

            // Add the configurable product to the cart
            $quote = $this->checkoutSession->getQuote();
            if (!$quote->getId()) {
                if ($this->customerSession->isLoggedIn()) {
                    $quote = $this->cartRepository->getActiveForCustomer($this->customerSession->getCustomerId());
                } else {
                    $quoteId = $this->cartManagement->createEmptyCart();
                    $quote = $this->quoteFactory->create()->load($quoteId);
                    $quote->setStore($this->storeManager->getStore());
                    $this->cart->setQuote($quote);
                }
            }

            $buyRequest = new DataObject([
                'product' => $requestProduct->getId(),
                'qty' => $qty,
                'super_attribute' => $superAttribute
            ]);

            $quote->addProduct($requestProduct, $buyRequest);
            $quote->collectTotals()->save();
            if ($source == "decide") {
                $this->checkoutSession->setNewEcomAiDecideSearchClicked($productId);
                $this->checkoutSession->setNewEcomAiDecideQuestionId($questionId);
            } elseif ($source == "discover") {
                $this->setFlag($quote->getId(), $productId, $questionId);
            }

            if (!$this->customerSession->isLoggedIn()) {
                $this->cartRepository->save($quote);
            }
            $this->cart->save();
            $items = $quote->getAllItems();
            foreach ($items as $item) {
                if ($requestProduct->getId() == $item->getProductId() && $item->getProductType() == 'configurable') {
                    foreach ($item->getChildren() as $child) {
                        if ($source == "decide") {
                            $child->setData('add_to_cart_from_decide', 1);
                        } elseif ($source == "discover") {
                            $child->setData('add_to_cart_from_discover', 1);
                        }
                    }
                }
                if ($requestProduct->getId() == $item->getProductId()) {
                    if ($source == "decide") {
                        $item->setData('add_to_cart_from_decide', 1);
                    } elseif ($source == "discover") {
                        $item->setData('add_to_cart_from_discover', 1);
                    }
                    $this->setDataForOrderApi($productId, $questionId, $item->getItemId());
                }
                $item->save();
            }

            $quote->save();
            if (!$this->customerSession->isLoggedIn()) {
                $this->cartRepository->save($quote);
            }

            $this->cart->save(); // Ensure cart session is saved
            return $resultJson->setData(['success' => true]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $resultJson->setData(['error' => true, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            return $resultJson->setData(['error' => true, 'message' => __('Unable to add product to cart.')]);
        }
    }

    /**
     * Set flag values in the session
     *
     * @param $quoteId
     * @param $productId
     * @param $questionId
     * @return void
     */
    protected function setFlag($quoteId, $productId, $questionId)
    {
        $this->checkoutSession->setNewEcomAiAddToCart(true);
        $this->checkoutSession->setNewEcomAiQuoteId($quoteId);
        $this->checkoutSession->setNewEcomAiProductId($productId);
        $this->checkoutSession->setNewEcomAiQuestionId($questionId);
    }

    /**
     * @param $productId
     * @param $questionId
     * @param $itemId
     * @return void
     */
    protected function setDataForOrderApi($productId, $questionId, $itemId)
    {
        $data = [
            'product_id' => $productId,
            'question_id' => $questionId,
            'item_id' => $itemId
        ];
        $sessionData = $this->checkoutSession->getOrderApiData();
        if (!$sessionData) {
            $sessionData = [];
        }
        // Add the new custom data to the session array
        $sessionData[] = $data;
        $this->checkoutSession->setOrderApiData($sessionData);
    }
}
