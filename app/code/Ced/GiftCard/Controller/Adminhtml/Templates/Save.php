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
namespace Ced\GiftCard\Controller\Adminhtml\Templates;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Serialize;


/**
 * Class Save
 * @package Ced\GiftCard\Controller\Adminhtml\Templates
 */
class Save extends Action
{
    /**
     * @var string
     */
    protected $basePath = 'giftcard';
    /**
     * @var string
     */
    protected $baseTmpPath = 'tmp/catalog/product';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection $customerModel
     */
    protected $customerModel;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory $salesRuleFactory
     */
    protected $salesRuleFactory;

    /**
     * @var \Ced\GiftCard\Model\GiftTemplate $giftTemplateFactory,
     */
    protected $_giftTemplateFactory;

    /**
     * @var \Ced\GiftCard\Model\ResourceModel\GiftTemplate\Collection $giftTemplateCollection
     */
    protected $_giftTemplateCollection;

    /**
     * @var \Magento\Framework\Image\AdapterFactory $imageFactory,
     */
    protected $_imageFactory;

    /**
     * @var Magento\Catalog\Model\Product\Media\Config
     */
    protected $config;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Magento\SalesRule\Model\RuleFactory $salesRuleFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\GiftCard\Model\GiftTemplateFactory $giftTemplateFactory,
        Database $Database,
        Filesystem $Filesystem,
        File $File,
        Serialize $Serialize,
        UploaderFactory $UploaderFactory, 
        LoggerInterface $LoggerInterface,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Catalog\Model\Product\Media\Config $config,
        \Ced\GiftCard\Model\ResourceModel\GiftTemplate\CollectionFactory $giftTemplateCollection
    )
    {
        $this->Database = $Database;
        $this->File = $File;
        $this->config = $config;
        $this->Serialize = $Serialize;
        $this->Filesystem = $Filesystem;
        $this->UploaderFactory = $UploaderFactory;
        $this->LoggerInterface = $LoggerInterface;
        $this->_giftTemplateCollection = $giftTemplateCollection;
        $this->_giftTemplateFactory = $giftTemplateFactory;
        $this->customerModel = $customerModel;
        $this->storeManager = $storeManager;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->_imageFactory = $imageFactory;
        parent::__construct($context);
    }

    /**
     * @param $code
     * @return mixed|string|string[]|null
     */
    public function getPreparedString($code){

        /*
         * @note replace special characters with the 'underscore(_)' other than curly_braces({})
        */
        $code = preg_replace('/[^a-zA-Z0-9{}]+/', '_', $code);

        /*
         * @note replace curly_braces({}) with the equal_to(=) symbol
        */
        $temp_1 = str_replace('{N}', '=N=', strtoupper($code));
        $temp_2 = str_replace('{S}', '=S=', strtoupper($temp_1));
        /*
         * @note replace special characters with the 'underscore(_)' other than equal_to(=)
        */
        $code = preg_replace('/[^a-zA-Z0-9=]+/', '_', $temp_2);

        /*
         * @note replace equal_to(=) with the curly_braces({}) symbol
        */
        $temp_1 = str_replace('=N=', '{N}', strtoupper($code));
        $code = str_replace('=S=', '{S}', strtoupper($temp_1));

        return $code;
    }
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try{
            $data = $this->getRequest()->getPostValue();
            $_giftTemplate = $this->_giftTemplateFactory->create();
 
            $data['code_template'] = str_replace(' ', '_', strtoupper($data['code_template']));

            $data['name'] = str_replace(' ', '_', strtoupper($data['name']));

            $data['name'] = preg_replace('/[^a-zA-Z0-9]+/', '_', $data['name']);
            $data['code_template'] = $this->getPreparedString($data['code_template']);
            /*
             * @Note: count variable to check if value of gift card is no more than that
             * */
            $c = 0;
            $returnParam = []; 

            if ($id = $this->getRequest()->getParam('id')){
                $_giftTemplate->load($id); 
                $c = 1;
                $returnParam = ['id' => $id];
            }
 
            $isActive = (null != $data['name'])?$data['name']:1;

            $_giftTemplate->setName($data['name']);
            $_giftTemplate->setCodeTemplate($data['code_template']);

            if (isset($data['product'])) {
                $images = $this->getImagesFromPostdata($data['product']['media_gallery']);
                if (is_array($images) && empty($images)){
                    $this->messageManager->addErrorMessage(__('Images are required for gift templates.'));
                    $id = $_giftTemplate->getId();
                    return $resultRedirect->setPath('*/*/edit', ['id'=> $id]);
                }
                $images = $this->Serialize->serialize($images);
                $_giftTemplate->setImages($images);
            }else{
                if (!isset($data['images'])) {
                    if (!isset($data['Image']) || $data['Image'] == 'image'){
                        $this->messageManager->addErrorMessage(__('Please Upload Images.'));
                        $id = $_giftTemplate->getId();
                        return $resultRedirect->setPath('*/*/edit', ['id'=> $id]);
                    }
                }
            }
            $_giftTemplate->setIsActive($isActive);
            
            $_giftTemplate->save();

            $this->messageManager->addSuccessMessage(__('Template Saved Successfully.'));
            if ($this->getRequest()->getParam('back') == 'edit'){
                $id = $_giftTemplate->getId(); 
                return $resultRedirect->setPath('*/*/edit', ['id'=> $id]);
            }else{
                return $resultRedirect->setPath('*/*/index');
            }
        }catch (\Exception $e){
            $param = [];
            if ($id = $_giftTemplate->getId()){
                $param = ['id' => $id];
            }

            $this->messageManager->addErrorMessage(__('Unable To save Template.'));
           
            return $resultRedirect->setPath('*/*/edit', $param);

        } 
    }

    /**
     * @param array $images
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getImagesFromPostdata($images = [])
    {
        $img = [];
        $c = 0;
        $mediaPath = $this->Filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath();
        
        $baseTmpPath = $mediaPath.$this->baseTmpPath;
        $basePath = $mediaPath.$this->basePath;
         
        foreach ($images as $key => $data) {
            
            if (is_numeric($key)) {
                if ($data['removed'] == 1) {
                }else{
                    $img[++$c] = $data;
                } 
            }else{
                try { 
                    if ($data['removed'] == 1) {
                        /*do nothing, coz it means nothing do here */
                    }else{
                        $copied = false;
                        $canCopy = false;
                        $tmpImage = $baseTmpPath.$data['file'];
                        $image = $baseTmpPath.$this->getFilenameFromTmp($data['file']);
                         
                        if ($this->File->isExists($tmpImage)) {
                            $canCopy = true;
                            $image = $tmpImage;
                        }else if($this->File->isExists($image)){
                            $canCopy = true;
                        }else{
                            $canCopy = false;
                        }
                         
                        /** @var \Magento\Framework\Filesystem $filesystem */ 
                        $_mediaDirectory = $this->Filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                        $imageName = $this->getFilenameFromTmp($data['file']);
                        
                        /** @var \Magento\Catalog\Model\Product\Media\Config $config */
                        $config = $this->config;
                        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
                        $mediaDirectory = $this->Filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                        $mediaPath = $mediaDirectory->getAbsolutePath();
                        $baseTmpMediaPath = $config->getBaseTmpMediaPath();
                        $from = $mediaPath.$baseTmpMediaPath . $imageName;
                        $to = $mediaPath . 'giftcard' . $imageName;

                        $data['file'] = $this->getFilenameFromTmp($data['file']);

                        if (file_exists($from)) {
                            $copied = $this->resizeAndSaveImage($from,$to, 600, 400);
                             
                            if ($copied) {
                                $img[++$c] = $data;
                            }           
                        }
                    }
                }catch(\Exception $e){
                    $this->messageManager->addErrorMessage(__('Error: During Uploading Images.'));
                } 
            }
        }

       return $img;
    }

    /**
     * @param $file
     * @return bool|string
     */
    protected function getFilenameFromTmp($file)
    {
        return strrpos($file, '.tmp') == strlen($file) - 4 ? substr($file, 0, strlen($file) - 4) : $file;
    }

    /**
     * @param $image
     * @param $to
     * @param null $width
     * @param null $height
     * @return bool
     */
    public function resizeAndSaveImage($image, $to, $width = null, $height = null)
    {
        try{
            $resizedpath = 'giftcard/';
            $absolutePath = $image;
            $imageResized = $to;

            //create image factory...
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(TRUE);
            $imageResize->backgroundColor([255,255,255]);
            $imageResize->keepTransparency(TRUE);
            $imageResize->keepFrame(TRUE);
            $imageResize->keepAspectRatio(TRUE);
            $imageResize->resize($width,$height);
            //destination folder
            $destination = $imageResized ;
            //save image
            $imageResize->save($destination);
            return $destination;
        }catch (\Exception $e){
            return false;
        }
    }

}
