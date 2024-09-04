<?php

namespace NewEcom\ShopSmart\Controller\Recommendations;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;

class ProductRemoveFromCart extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var Cart
     */
    protected Cart $cart;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Cart $cart
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     * Remove product from cart url
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = json_decode($this->getRequest()->getContent(), true);

        try {
            $productId = $data['productId'];
            $quote = $this->cart->getQuote();
            $items = $quote->getItems();
            foreach ($items as $item) {
                if ($item->getProductId() == $productId) {
                    $quote->removeItem($item->getId());
                    $quote->save();
                    $this->cart->save();
                }
            }

            return $result->setData(['success' => true]);
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
