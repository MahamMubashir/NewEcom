<?php

namespace NewEcom\ShopSmart\Controller\Recommendations;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use NewEcom\ShopSmart\Helper\SyncManagement as Data;
use NewEcom\ShopSmart\Model\Log\Log;

class UploadImageUrl extends Action
{
    /**
     * Save image path
     */
    protected const UPLOAD_IMAGE_PATH = "NewEcom/ShopSmart/images/";
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
     * @param Context $context
     * @param Http $http
     * @param JsonFactory $jsonFactory
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param Data $dataHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context               $context,
        Http                  $http,
        JsonFactory           $jsonFactory,
        UploaderFactory       $uploaderFactory,
        Filesystem            $filesystem,
        Data                  $dataHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->http = $http;
        $this->jsonFactory = $jsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Upload image url controller
     *
     * @return ResponseInterface|Json|ResultInterface|string|void
     */
    public function execute()
    {
        try {
            if ($this->http->isAjax()) {
                $resultJson = $this->jsonFactory->create();
                $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'webp', 'svg']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $mediaDirectory = $this->filesystem
                    ->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath(self::UPLOAD_IMAGE_PATH);
                $result = $uploader->save($mediaDirectory);
                if (!$result) {
                    Log::Error(__('File cannot be saved to path: $1', $mediaDirectory));
                }
                $filePath = self::UPLOAD_IMAGE_PATH . $result['file'];
                $fileUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $filePath;
                if ($fileUrl) {
                    return $resultJson->setData(["response" => $fileUrl]);
                } else {
                    return $resultJson->setData(["error" => "No url found"]);
                }
            }
        } catch (\Exception $e) {
            Log::Error($e->getMessage());
        }
    }
}
