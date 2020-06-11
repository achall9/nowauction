<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Helper;

/**
 * Class Image
 * @package Ced\CsMarketplace\Helper
 */
class Image extends Data
{

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * Image constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ValueInterface $value
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollFactory
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Framework\App\State $state
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Indexer\Model\Processor $processor
     * @param \Magento\Framework\App\Config\Element $element
     * @param \Ced\CsMarketplace\Model\Vshop $vshop
     * @param \Magento\Catalog\Model\Product\WebsiteFactory $productWebsiteFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtils
     * @param Mail $mailHelper
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ValueInterface $value,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\ActionFactory $actionFactory,
        \Magento\Indexer\Model\ProcessorFactory $processorFactory,
        \Magento\Catalog\Model\Product\Website $website,
        \Ced\CsMarketplace\Model\Vshop $vshop,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\Store $store,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
    )
    {
        parent::__construct(
            $filterManager, $moduleRegistry, $cacheTypeList, $cacheFrontendPool, $request, $productMetadata, $storeManager, $value, $transaction, $requestInterface,
            $state, $websiteFactory, $actionFactory, $processorFactory, $website, $vshop, $resourceConnection,  $deploymentConfig, $notificationFactory,
            $vproductsFactory, $vendorFactory, $stringUtils, $vpaymentFactory, $store, $context
        );
        $this->request = $requestInterface;
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * @param bool $attributes
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function UploadImage($attributes = false)
    {
        if (!$attributes) {
            $imagefields = ['profile_picture','company_logo','company_banner'];
            foreach($imagefields as $fieldName) {
                $this->UploadImagebyName($fieldName);
            }
        } else {
            foreach($attributes as $attribute) {
                if ($attribute->getFrontendInput() == 'file')
                {
                    $this->UploadFilebyName($attribute->getAttributeCode());
                } else {
                    $this->UploadImagebyName($attribute->getAttributeCode());
                }
            }
        }
        return $this->data;
    }

    /**
     * @param $fieldName
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function UploadImagebyName($fieldName)
    {

        $vendorPost = $this->request->getParam('vendor');
        $mediaDirectory = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath('ced/csmaketplace/vendor');
        $allowed_type = ['jpg','jpeg','gif','png'];
        if($this->_moduleManager->isEnabled('Ced_CsVendorAttribute')){
            $allowed_type = explode(',',$this->_scopeConfigManager->getValue('ced_csvendorattribute/vendorattribute/allowed_image_type',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->getStore()->getId()));
        }

        $imagePath = false;
        try {

            $Files = $this->request->getFiles()->toArray();

            $file = [];
            if(isset($Files['vendor'])){
                $file = $Files['vendor'][$fieldName];

            }


            $uploader = $this->uploaderFactory->create(array('fileId' => "vendor[{$fieldName}]"));
            $uploader->setAllowedExtensions($allowed_type); // or pdf or anything
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $fileData = $uploader->validateFile();
            $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            $fileName = $fieldName.time().'.'.$extension;
            $flag = $uploader->save($path, $fileName);
            $imagePath = true;
            $this->data[$fieldName] = 'ced/csmaketplace/vendor/'.$fileName;
        } catch(\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        if (!$imagePath) {
            if (isset($vendorPost[$fieldName]['delete']) && $vendorPost[$fieldName]['delete'] == 1) {
                $this->data[$fieldName] = '';
                $imageName = explode('/', $vendorPost[$fieldName]['value']);
                $imageName = $imageName[count($imageName)-1];
                unlink($path.'/'.$imageName);
            } else {
                unset($this->data[$fieldName]);
            }
        }
    }

    /**
     * @param $fieldName
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function UploadFilebyName($fieldName)
    {
        $vendorPost = $this->request->getParam('vendor');
        $mediaDirectory = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath('ced/csmaketplace/vendor');
        $allowed_type = ['jpg','jpeg','gif','png','pdf','doc','docx'];
        if($this->_moduleManager->isEnabled('Ced_CsVendorAttribute')){
            $allowed_type = explode(',',$this->_scopeConfigManager->getValue('ced_csvendorattribute/vendorattribute/allowed_file_type',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->getStore()->getId()));
        }
        $imagePath = false;
        try {
            $uploader = $this->uploaderFactory->create(array('fileId' => "vendor[{$fieldName}]"));
            $uploader->setAllowedExtensions($allowed_type);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $fileData = $uploader->validateFile();
            $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            $fileName = $fieldName.time().'.'.$extension;
            $flag = $uploader->save($path, $fileName);
            $imagePath = true;
            $this->data[$fieldName] = 'ced/csmaketplace/vendor/'.$fileName;
        } catch(\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        if (!$imagePath) {
            if (isset($vendorPost[$fieldName]['delete']) && $vendorPost[$fieldName]['delete'] == 1) {
                $this->data[$fieldName] = '';
                $imageName = explode('/', $vendorPost[$fieldName]['value']);
                $imageName = $imageName[count($imageName)-1];
                unlink($path.'/'.$imageName);
            } else {
                unset($this->data[$fieldName]);
            }
        }
    }

}
