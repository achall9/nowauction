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

namespace Ced\GiftCard\Block\Adminhtml\Form\Gallery;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\DataProviders\ImageUploadConfig as ImageUploadConfigDataProvider;

/**
 * Class Content
 * @package Ced\GiftCard\Block\Adminhtml\Form\Gallery
 */
class Content extends \Magento\Backend\Block\Widget

{

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

    /**
     * @var string
     */
    protected $_template = 'Ced_GiftCard::helper/gallery.phtml';


    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ImageUploadConfigDataProvider
     */
    private $imageUploadConfigDataProvider;


    /**
     * Content constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Registry $registry
     * @param Serialize $Serialize
     * @param StoreManagerInterface $StoreManagerInterface
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Registry $registry,
        Serialize $Serialize,
        StoreManagerInterface $StoreManagerInterface,
        array $data = []
    )
    {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_mediaConfig = $mediaConfig;
        $this->_coreRegistry = $registry;
        $this->Serialize = $Serialize;
        $this->storeManager = $StoreManagerInterface;

        $this->imageUploadConfigDataProvider = !class_exists(ImageUploadConfigDataProvider::class)
            ?: ObjectManager::getInstance()->get(ImageUploadConfigDataProvider::class);

        $this->mediaUrl = $StoreManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'giftcard';
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget
     */
    protected function _prepareLayout()
    {
        $this->addChild('uploader',
            \Magento\Backend\Block\Media\Uploader::class,
            ['image_upload_config_data' => $this->imageUploadConfigDataProvider]);

        $this->getUploader()->getConfig()
            ->setUrl(
                $this->_urlBuilder->addSessionParam()->getUrl('catalog/product_gallery/upload')
            )->setFileField(
                'image'
            )->setFilters(
                [
                    'images' => [
                        'label' => __('Images (.gif, .jpg, .png)'),
                        'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                    ],
                ]
            );

        return parent::_prepareLayout();
    }


    /**
     * @return bool|AbstractBlock
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }


    /**
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }


    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }


    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }


    /**
     * @return string
     */
    public function getImagesJson()
    {
        $value = [];
        $data = $this->_coreRegistry->registry('ced_gift_card');

        if (null !== $this->_coreRegistry->registry('ced_gift_card')) {
            if (null !== $data->getImages()) {
                $value = $this->Serialize->unserialize($data->getImages());
            }
        }

        if (is_array($value) &&
            count($value)
        ) {
            $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $images = $this->sortImagesByPosition($value);
            foreach ($images as &$image) {
                $image['url'] = $this->mediaUrl . '/' . $this->_prepareFile($image['file']);
                $mediaPath = 'giftcard/' . $this->_prepareFile($image['file']);
                $fileHandler = $directory->stat($mediaPath);
                $image['size'] = $fileHandler['size'];

            }
            return $this->_jsonEncoder->encode($images);
        }
        return '[]';
    }

    /**
     * @param string $file
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * @param $images
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort($images, function ($imageA, $imageB) {
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
        }
        return $images;
    }


    /**
     * @return string
     */
    public function getImagesValuesJson()
    {
        $values = [];
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $values['image'] = 'image';
        }
        return $this->_jsonEncoder->encode($values);
    }


    /**
     * @return mixed
     */
    public function getImageTypes()
    {
        $imageTypes['image'] = [
            'code' => 'image',
            'value' => 'image',
            'label' => 'Image',
            'scope' => __('Global'),
            'name' => 'Image',
        ];
        return $imageTypes;
    }


    /**
     * @return bool
     */
    public function hasUseDefault()
    {
        foreach ($this->getMediaAttributes() as $attribute) {
            if ($this->getElement()->canDisplayUseDefault($attribute)) {
                return true;
            }
        }

        return false;
    }


    /**
     * @return array
     */
    public function getMediaAttributes()
    {
        return ['image'];
    }


    /**
     * @return string
     */
    public function getImageTypesJson()
    {
        return $this->_jsonEncoder->encode($this->getImageTypes());
    }
}