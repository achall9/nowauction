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
namespace Ced\GiftCard\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Product View block
 */
class Preview extends \Magento\Framework\View\Element\Template
{


    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @deprecated 101.1.0
     */
    protected $priceCurrency;


    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var \Ced\GiftCard\Model\GiftTemplate
     */
    protected $giftTemplate;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $_serialize;

    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    protected $_logo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem ;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\GiftCard\Model\GiftTemplate $giftTemplate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Serialize $Serialize,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        array $data = []
    ) {
        $this->_logo = $logo;
        $this->_serialize = $Serialize;
        $this->giftTemplate = $giftTemplate;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
        $this->_customerRepositoryInterface = $_customerRepositoryInterface;

        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        parent::__construct(
            $context,
            $data
        );

    }
    /**
     * Return product type
     *
     * @return string
     */
    public function getProductTypeId(){
        return $this->getProduct()->getTypeId();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCurrency(){

        return $this->priceCurrency->getCurrencySymbol(
            null,
            $this->_storeManager->getStore()->getCurrentCurrency()->getCode()
        ); 
    }
    /**
     * Return product type
     *
     * @return string
     */
    public function isGiftCardProduct(){
        return $this->getProduct()->getTypeId() == 'giftcard';
    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {
        return $this->_logo->getLogoSrc();
    }
    /**
     * Get logo text
     *
     * @return string
     */
    public function getLogoAlt()
    {
        return $this->_logo->getLogoAlt();
    }

    /**
     * Return product minimum amount of gift card
     *
     * @return string
     */
    public function getMinimumGiftAmount(){
        return $this->getProduct()->getGiftMinAmount();
    }

    /**
     * Return product minimum amount of gift card
     *
     * @return string
     */
    public function getMaximumGiftAmount(){
        return $this->getProduct()->getGiftMaxAmount();
    }
    /**
     * Return product Gift Template ID
     *
     * @return string
     */
    public function getGiftTemplate(){
        return $this->getProduct()->getGiftTemplate();
    }

    /**
     * Return Gift Template Data
     *
     * @return object || null
     */
    public function getTemplateData()
    {
        if ($this->isGiftCardProduct()) {
            return $this->giftTemplate->load($this->getGiftTemplate(), 'id');
        }
        return null;
    }
    /**
     * Return Gift Template Images
     *
     * @return array()
     */
    public function getGiftTemplateImages()
    {
        if (null !== $this->getTemplateData()) {
            if ($this->getTemplateData()->getImages() !== null) {
                $images = $this->_serialize->unserialize($this->getTemplateData()->getImages());
                $files = array_column($images, 'file');
                foreach ($images as &$image){
                    $image['resizedfileurl'] = $this->resize($image['file'], 260, 180);
                }
                return $images;
            }
        }
        return [];
    }
    /**
     * Get Post
     *
     * @return array()
     */
    public function getPostValue()
    {
        return $this->getRequest()->getPostValue();
    }

    /**
     * @param $image
     * @param null $width
     * @param null $height
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resize($image, $width = null, $height = null)
    {
        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath($resizedpath).$image;
 
        $resizedpath = 'resized/giftcard/'.$width.'/'.$height.'/';
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('giftcard').$image;

        $imageResize = $this->_imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->keepTransparency(TRUE);
        $imageResize->keepAspectRatio(TRUE);
        $imageResize->resize($width,$height);
        $imageResize->keepFrame(TRUE);
        $imageResize->constrainOnly(TRUE);
        $imageResize->backgroundColor([255,255,255]);
        //destination folder
        $destination = $imageResized ;
        //save image
        $imageResize->save($destination);

        $resizedURL = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$resizedpath.$image;

        return $resizedURL;
    }
    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $category = $this->_coreRegistry->registry('current_category');

        $identities = $this->getProduct()->getIdentities();
        if ($category) {
            $identities[] = Category::CACHE_TAG . '_' . $category->getId();
        }
        return $identities;
    }

    /**
     * Return customer name
     *
     * @return string
     */
    public function getCustomerName(){
        $customerName = '';
        $customerId = $this->customerSession->getData('customer_id');
        if($customerId)
        {
            $customer = $this->_customerRepositoryInterface->getById($customerId);
            $customerName = $customer->getFirstname();
        }
        return $customerName;
    }

}