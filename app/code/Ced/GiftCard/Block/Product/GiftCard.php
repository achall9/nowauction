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
 * @category  Ced
 * @package   Ced_GiftCard
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\GiftCard\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Class GiftCard
 * @package Ced\GiftCard\Block\Product
 */
class GiftCard extends \Magento\Catalog\Block\Product\AbstractProduct
    implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $string;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
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
     * @var
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem ;

    /**
     * @var null
     */
    protected $_giftMinAmount = null;
    /**
     * @var null
     */
    protected $_giftMaxAmount = null;
    /**
     * @var array
     */
    protected $_priceSlabs = [];

    /**
     * @var Object | NULL
     */
    protected $_templateData = null;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
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
        $this->_productHelper = $productHelper;
        $this->urlEncoder = $urlEncoder;
        $this->_jsonEncoder = $jsonEncoder;
        $this->productTypeConfig = $productTypeConfig;
        $this->string = $string;
        $this->_localeFormat = $localeFormat;
        $this->customerSession = $customerSession;

        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
        $this->_customerRepositoryInterface = $_customerRepositoryInterface;
        $this->cart = $context->getCartHelper()->getCart();
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
     * @param int $amount
     * @return float
     */
    public function getFormatedPrice($amount = 0)
    {
        return $this->priceCurrency->convert($amount);
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
     * @return array|bool|float|int|mixed|string|null
     */
    public function getSelecteddata(){
        try{

            if ($id = $this->getRequest()->getParam('id')){
                $quoteitem = $this->cart->getQuote()->getItemById($id);
                if ($quoteitem &&
                    $quoteitem->getProductType() &&
                    $quoteitem->getCedGiftcarddata()
                ){
                    $data = $this->_serialize->unserialize($quoteitem->getCedGiftcarddata());
                    return $data;
                }else{
                    return false;
                }
            }
        }catch (\Exception $e){
            return false;
        }
        return false;

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
        if (!$this->_giftMinAmount){
            $this->_giftMinAmount = $this->getFormatedPrice($this->getProduct()->getGiftMinAmount());
        }

        return $this->_giftMinAmount;
    }

    /**
     * Return product minimum amount of gift card
     *
     * @return string
     */
    public function getMaximumGiftAmount(){
 
        if (!$this->_giftMaxAmount){ 
            $this->_giftMaxAmount = $this->getFormatedPrice($this->getProduct()->getGiftMaxAmount());
        }

        return $this->_giftMaxAmount;
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
       if($this->_templateData == null){
           if ($this->isGiftCardProduct()) {
               $this->_templateData = $this->giftTemplate->load($this->getGiftTemplate(), 'id');
           }
       }
       return $this->_templateData;
    }
    /**
     * Return Gift card avaliable price spabs
     *
     * @return array
     */
    public function getGiftPriceSlabs()
    { 
        if (empty($this->_priceSlabs)){
            try{
                $priceslabs = $this->getProduct()->getGiftPriceSlabs();
                $priceslabs = explode(',', $priceslabs);
                foreach ($priceslabs as $price){
                    $price = $this->getFormatedPrice($price);
                    if ($price >= $this->getMinimumGiftAmount() &&
                        $price <= $this->getMaximumGiftAmount() &&
                        !in_array($price, $this->_priceSlabs)
                    ) {
                        $this->_priceSlabs[] = $price;
                    }
                }
            }catch(\Exception $e){
                $this->_priceSlabs = [];
            }
        }
        return $this->_priceSlabs;
    }
    /**
     * Return Gift Template Images
     *
     * @return array()
     */
    public function getGiftTemplateImages()
    {
        $images = [];
        if (null !== $this->getTemplateData()) {
            if ($this->getTemplateData()->getImages() !== null) {
                $images = $this->_serialize->unserialize($this->getTemplateData()->getImages());
                foreach ($images as &$image){
                    $image['resizedfileurl'] = $this->resize($image['file'], 260, 180);
                }
            } 
        }
        return $images;
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
        $resizedpath = 'resized/giftcard/'.$width.'/'.$height.'/';
        $absolutePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('giftcard').$image;

        $imageResized = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($resizedpath).$image;
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
        $identities = $this->getProduct()->getIdentities();
        $category = $this->_coreRegistry->registry('current_category');
        if ($category) {
            $identities[] = Category::CACHE_TAG . '_' . $category->getId();
        }
        return $identities;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerName(){
        $name = '';
        if($this->customerSession->getData('customer_id'))
        {
            $customer = $this->_customerRepositoryInterface->getById(
                $this->customerSession->getData('customer_id')
            );
            $name = $customer->getFirstname();
        } 
        return $name;
    }
}