<?php

namespace NewEcom\ShopSmart\Cron;

use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Io\File;

class DeleteUploadImageMediaFolder
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var File
     */
    protected File $file;

    /**
     * @param LoggerInterface $logger
     * @param File $file
     */
    public function __construct(
        LoggerInterface $logger,
        File $file
    ) {
        $this->logger = $logger;
        $this->file = $file;
    }

    /**
     * Execute the cron job to delete files in the media folder
     *
     * @return void
     * @throws FileSystemException
     */
    public function execute()
    {
        $directory = 'pub/media/NewEcomAI/ShopSmart/images';
        if (is_dir($directory)) {
            $this->clearDirectory($directory);
            $this->logger->info('Media folder cleared successfully.');
        } else {
            $this->logger->info('Media folder does not exist.');
        }
    }

    /**
     * Recursively clear the directory
     *
     * @param string $dir
     * @throws FileSystemException
     */
    protected function clearDirectory($dir)
    {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectory($file);
                rmdir($file);
            }
        }
    }
}
