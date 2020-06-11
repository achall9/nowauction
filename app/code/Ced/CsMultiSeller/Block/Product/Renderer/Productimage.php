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
 * @package     Ced_CsMultiSeller
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiSeller\Block\Product\Renderer;

/**
 * Class Productimage
 * @package Ced\CsMultiSeller\Block\Product\Renderer
 */
class Productimage extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Productimage constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return Image
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $storeId = $this->getRequest()->getParam('store', 0);
        $html = '';
        $_product = $this->productFactory->create()->setStoreId($storeId)->load($row->getEntityId());
        if ($_product && $_product->getId()) {
            if ($storeId == 0 && $this->_storeManager->isSingleStoreMode())
                $productUrl = $_product->getProductUrl();
            else if ($storeId != 0)
                $productUrl = $_product->getUrlInStore(array('_store' => $storeId));

            if (($row->getCheckStatus() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS) && (($row->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED && $storeId != 0) || ($storeId == 0 && $this->_storeManager->isSingleStoreMode()))) {
                $image = $this->imageHelper->init($_product, 'thumbnail', array('type' => 'small_image'))->getUrl();
                $html = '<div style="text-align:center"><a href="' . $productUrl . '" target="_blank">';
                $html .= '<img title="' . $row->getName() . '" id=' . $row->getId() . " width='70' height='35' src='" . $image . "'/></a></div>";
                $html .= '<div><a style="color:#337ab7;" target="_blank" href="' . $productUrl . '">' . $row->getName() . '</a></div>';
            } else {
                $image = $this->imageHelper->init($_product, 'thumbnail', array('type' => 'small_image'))->getUrl();
                $html = '<div style="text-align:center"><img id=' . $row->getId() . " width='70' height='35' src='" . $image . "'/></div><div>";
                $html .= $row->getName() . '</div>';
            }
        }
        return $html;
    }

}

?>