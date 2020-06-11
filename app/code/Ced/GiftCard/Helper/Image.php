<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_GiftCard
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Helper;
 
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Image
 * @package Ced\GiftCard\Helper
 */
class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
    const TEMP_FOLDER = 'giftcard/tmp/';
    /**
     * @var \Magento\Framework\Math\Random $random
     */
    public $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Filesystem $filesystem,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManagerInterface
    ){
        parent::__construct($context);
        $this->_filesystem = $filesystem;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * @param $image
     * @param string $destinationFolder
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function retreiveImage($image, $destinationFolder = self::TEMP_FOLDER){

        $store = $this->storeManagerInterface->getStore();
        $mediaUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $image = $mediaUrl.$destinationFolder.$image;
        return $image;
       
    }

    /**
     * @param $input
     * @param string $destinationFolder
     * @param bool $filenameOnly
     * @return string
     * @throws \Exception
     */
    public function uploadImageAndGetUrl($input, $destinationFolder = self::TEMP_FOLDER, $filenameOnly = false)
    {
        try {

            $uploader = $this->uploaderFactory->create(['fileId' => $input]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath($destinationFolder);
            $result = $uploader->save($path);
            $image = $result['file'];
            if (!$filenameOnly){
                $store = $this->storeManagerInterface->getStore();
                $mediaUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $image = $mediaUrl.$destinationFolder.$image;
            }
            return $image;

        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                throw new \Exception($e->getMessage());
            }
        }
        return '';
    }
}
