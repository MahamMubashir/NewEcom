<?php

namespace NewEcom\ShopSmart\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use NewEcom\ShopSmart\Model\Config as ConfigHelper;

/**
 * Check Account Validation
 */
class EcomAccountValidation extends Action
{
    /**
     * @var Http
     */
    private Http $http;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $helperData;

    /**
     * @param Http $http
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConfigHelper $helperData
     */
    public function __construct(
        Http        $http,
        Context     $context,
        JsonFactory $resultJsonFactory,
        ConfigHelper        $helperData
    ) {
        $this->http = $http;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Validate Account credentials
     *
     * @return ResponseInterface|Json|ResultInterface|void
     */
    public function execute()
    {
        if ($this->http->isAjax()) {
            $resultJson = $this->resultJsonFactory->create();
            $endpoint = "api/oauth/v1/token";
            $postData = json_encode([
                'username' => $this->helperData->getShopSmartUserName(),
                'password' => $this->helperData->getShopSmartUserPassword(),
                'userId' => $this->helperData->getShopSmartUserId()
            ]);
            $response = $this->helperData->sendApiRequest($endpoint, "POST", false, $postData,);
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['token'])) {
                return $resultJson->setData(['status' => true, 'message' => "Account Validated Successfully"]);
            }
        }
    }
}
