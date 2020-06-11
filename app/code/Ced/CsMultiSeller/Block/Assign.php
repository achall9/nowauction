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

namespace Ced\CsMultiSeller\Block;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreFactory;

/**
 * Class Assign
 * @package Ced\CsMultiSeller\Block
 */
class Assign extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var StoreFactory
     */
    public $storeFactory;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    public $currency;

    /**
     * Assign constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param StoreFactory $storeFactory
     * @param \Magento\Framework\Locale\Currency $currency
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Locale\Currency $currency,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory
    )
    {
        $this->productFactory = $productFactory;
        $this->storeFactory = $storeFactory;
        $this->currency = $currency;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * Get set collection of products
     *
     */
    public function _construct()
    {
        parent::_construct();
        $id = $this->getRequest()->getParam('id', 0);
        if ($id) {
            $prod = $this->productFactory->create()->load($id);
            $this->setProductName($prod->getName());
        }
        $this->setTitle(__('Assign Product'));
    }

    /**
     * Return Back Url
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/product/new');
    }

    /**
     * Return Save Url
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/duplicate', array('_current' => true, 'back' => null));
    }
}
