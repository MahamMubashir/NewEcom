<?php

namespace NewEcom\ShopSmart\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session as CheckoutSession;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Log\Log;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Observes the `checkout_cart_product_add_after` event.
 */
class AddToCart implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @param CheckoutSession $checkoutSession
     * @param Data $helper
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Data $helper,
        CartRepositoryInterface $cartRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Observer for checkout_cart_product_add_after.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $newComProduct = $this->checkoutSession->getNewEcomAiAddToCart();
        $quoteId = $this->checkoutSession->getNewEcomAiQuoteId();
        $productId = $this->checkoutSession->getNewEcomAiProductId();
        $questionId = $this->checkoutSession->getNewEcomAiQuestionId();
        $decideAddToCart = $this->checkoutSession->getNewEcomAiDecideSearchClicked();
        $decideQuestionId = $this->checkoutSession->getNewEcomAiDecideQuestionId();
        if ($newComProduct && $quoteId && $productId) {
            $data = [
                "userId" => $this->helper->getShopSmartUserId(),
                "questionId" => $questionId,
                "productId" => $productId
            ];
            $endpoint = "api/questions/discovery/addtocart";
            $response = $this->helper->sendApiRequest($endpoint, "POST", true, json_encode($data));
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['response']['status'])
                && $responseData['response']['status'] == 'success') {
                Log::Info("The product has been added to cart from discover and synced successfully.");
            }
            $this->checkoutSession->setNewEcomAiAddToCart(null);
            $this->checkoutSession->setNewEcomAiQuoteId(null);
            $this->checkoutSession->setNewEcomAiProductId(null);
            $this->checkoutSession->setNewEcomAiQuestionId(null);
        }
        if (isset($decideAddToCart) && isset($decideQuestionId)) {
            $data = [
                "userId" => $this->helper->getShopSmartUserId(),
                "questionId" => $decideQuestionId
            ];
            $endpoint = "api/questions/decide/addtocart";
            $response = $this->helper->sendApiRequest($endpoint, "POST", true, json_encode($data));
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['response']['status'])
                && $responseData['response']['status'] == 'success') {
                Log::Info("The product has been added to cart from decide and synced successfully.");
            }
            $this->checkoutSession->setNewEcomAiDecideSearchClicked(null);
            $this->checkoutSession->setNewEcomAiDecideQuestionId(null);
        }
        if (isset($decideAddToCart)) {
            $quote = $this->checkoutSession->getQuote();
            $items = $observer->getEvent()->getItems();
            foreach ($items as $item) {
                if ($decideAddToCart == $item->getProductId() && $item->getProductType() == 'configurable') {
                    foreach ($item->getChildren() as $child) {
                        $child->setData('add_to_cart_from_decide', 1);
                    }
                }
                if ($decideAddToCart == $item->getProductId()) {
                    $item->setData('add_to_cart_from_decide', 1);
                    $this->setDataForOrderApi($decideAddToCart, $decideQuestionId, $item->getItemId());
                }
            }

            $quote->save();
            $this->cartRepository->save($quote);
        }
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
