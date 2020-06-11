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
 * @package     Ced_CsMessaging
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const CUSTOMER_AS_SENDER = 'customer';
    const VENDOR_AS_SENDER = 'vendor';
    const ADMIN_AS_SENDER = 'admin';
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';
    const ADMIN_ID = 0;
    const SEND_MAIL_CHECKED = 1;
    const MAIL_SEND = 2;
    const IS_MESSAGING_ENABLED = 'ced_csmarketplace/general/messaging_active';

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     */
    public function __construct(Context $context,
                                \Magento\Backend\Model\UrlInterface $backendUrl,
                                \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
                                \Magento\Framework\Filesystem $filesystem,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        parent::__construct($context);
        $this->backendUrl = $backendUrl;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->_scopeConfigManager = $context->getScopeConfig();
    }

    /**
     * @return mixed
     */
    public function getWysiwygConfigArrayFrontend()
    {
        $configArr['width'] = "100%";
        $configArr['height'] = "200px";
        $configArr['tinymce4']['plugins'] = 'advlist autolink lists link charmap media noneditable table contextmenu paste code help table';
        $configArr['tinymce4']['toolbar'] = 'formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap';
        return $configArr;
    }

    /**
     * @return mixed
     */
    public function getWysiwygConfigArrayAdmin()
    {
        $configArr['width'] = "100%";
        $configArr['height'] = "200px";
        $configArr['plugins'][0]['name'] = "image";
        $configArr['add_images'] = true;
        $configArr['files_browser_window_url'] = $this->backendUrl->getUrl(
            'cms/wysiwyg_images/index'
        );
        $configArr['files_browser_window_width'] =  "1000";
        $configArr['files_browser_window_height'] =  "600";
        $configArr['tinymce4']['plugins'] = 'advlist autolink lists link charmap media noneditable table contextmenu paste code help table';
        $configArr['tinymce4']['toolbar'] = 'formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap';
        return $configArr;
    }

    public function saveImages($threadId,$chat_images)
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath('csmessaging/chat_images/');

        $result = [];
        foreach( $chat_images as $_image) {
            if (!empty($_image) && !empty($_image['name']) && empty($_image['error'])) {
                try {
                    $uploader = $this->uploader->create(array('fileId' => $_image));
                    $uploader->setAllowedExtensions(array('png', 'jpg', 'jpeg','doc','docx','zip','xlsx','csv','pdf'));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $fileData = $uploader->validateFile();
                    if( !empty($fileData['name']) ){
                        $fileName = $threadId . '_' . preg_replace("/[^a-z0-9\_\-\.]/i",'',$fileData['name']);

                        $uploader->save($path, $fileName);
                        $result['filename'][] = $fileName;
                    }
                } catch (\Exception $e) {
                    $result = ['error'=>true,'message'=>$e->getMessage()];
                    continue;
                }
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getMessageImagePath()
    {
        $imagePath = $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $imagePath .'csmessaging/chat_images/';

    }

    /**
     * @param $images
     * @return mixed
     */
    public function getMessageImages($images)
    {
        return $this->jsonHelper->jsonDecode($images);
    }

    public function getStoreConfig($path, $storeId = null)
    {
        $store = $this->_storeManager->getStore($storeId);
        return $this->_scopeConfigManager->getValue($path, 'store', $store->getCode());
    }
}
