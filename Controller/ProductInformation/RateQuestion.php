<?php

namespace NewEcom\ShopSmart\Controller\ProductInformation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface as Response;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;

class RateQuestion extends Action
{

    /**
     * Decide Rate Question API Endpoint
     */
    protected const DECIDE_RATE_QUESTION_API_ENDPOINT = "api/questions/decide/rate";

    /**
     * @var Http
     */
    private Http $http;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var Data
     */
    private Data $dataHelper;

    /**
     * @param Context $context
     * @param Http $http
     * @param JsonFactory $resultJsonFactory
     * @param Data $dataHelper
     */
    public function __construct(
        Context     $context,
        Http        $http,
        JsonFactory $resultJsonFactory,
        Data        $dataHelper
    ) {
        $this->http = $http;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Rate question controller
     *
     * @return Response|Json|ResultInterface|void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $score = $params['score'];
        $questionId = $params['questionId'];
        $userId = $this->dataHelper->getShopSmartUserId();
        if ($this->http->isAjax()) {
            $resultJson = $this->resultJsonFactory->create();
            $data = [
                'userId' => $userId,
                "questionId" => $questionId,
                "score" => $score
            ];
            $endpoint = self::DECIDE_RATE_QUESTION_API_ENDPOINT;
            $response = $this->dataHelper->sendApiRequest($endpoint, "POST", true, json_encode($data));
            $responseData = json_decode($response, true);
            return $resultJson->setData(['response' => $responseData]);
        }
    }
}
