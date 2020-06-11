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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vorders;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Ced\CsMarketplace\Model\Session as MarketplaceSession;

class ListOrders extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var MarketplaceSession
     */
    public $marketplacesession;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid
     */
    protected $grid;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * ListOrders constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param MarketplaceSession $mktSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid $grid
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        MarketplaceSession $mktSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid $grid,
        \Ced\CsMarketplace\Model\Vorders $vorders
    )
    {
        $this->marketplacesession = $mktSession;
        $this->_priceCurrency = $priceCurrency;
        $this->grid = $grid;
        $this->vorders = $vorders;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $ordersCollection = [];
        if ($vendorId = $this->getVendorId()) {
            $ordersCollection = $this->getVendor()->getAssociatedOrders()->setOrder('id', 'DESC');
            $main_table = 'main_table';
            $order_total = 'order_total';
            $shop_commission_fee = 'shop_commission_fee';
            $ordersCollection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$order_total} - {$main_table}.{$shop_commission_fee})")));

            $filterCollection = $this->filterOrders($ordersCollection);
            $this->setVorders($filterCollection);
        }

    }


    public function filterOrders($ordersCollection)
    {
        $params = $this->marketplacesession->getData('order_filter');
        $main_table = 'main_table';
        $order_total = 'order_total';
        $shop_commission_fee = 'shop_commission_fee';
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $field => $value) {
                if ($field == '__SID')
                    continue;
                if (is_array($value)) {
                    if (isset($value['from']) && urldecode($value['from']) != "") {
                        $from = urldecode($value['from']);
                        if ($field == 'created_at') {
                            $from = date("Y-m-d 00:00:00", strtotime($from));
                        }
                        if ($field == 'net_vendor_earn')
                            $ordersCollection->getSelect()->where("({$main_table}.{$order_total}- {$main_table}.{$shop_commission_fee}) >='" . $from . "'");
                        else
                            $ordersCollection->addFieldToFilter($main_table . '.' . $field, array('gteq' => $from));
                    }
                    if (isset($value['to']) && urldecode($value['to']) != "") {
                        $to = urldecode($value['to']);
                        if ($field == 'created_at') {
                            $to = date("Y-m-d 59:59:59", strtotime($to));
                        }
                        if ($field == 'net_vendor_earn')
                            $ordersCollection->getSelect()->where("({$main_table}.{$order_total}- {$main_table}.{$shop_commission_fee}) <='" . $to . "'");
                        else
                            $ordersCollection->addFieldToFilter($main_table . '.' . $field, array('lteq' => $to));
                    }
                } else if (urldecode($value) != "") {
                    $ordersCollection->addFieldToFilter($main_table . '.' . $field, array("like" => '%' . urldecode($value) . '%'));
                }
            }
        }
        return $ordersCollection;
    }


    /**
     * prepare list layout
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pagerBlock = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'custom.pager');
        $pagerBlock->setAvailableLimit([5 => 5, 10 => 10, 20 => 20, 'all' => 'all']);
        $pagerBlock->setCollection($this->getVorders());
        $this->setChild('pager', $pagerBlock);
        return $this;
    }

    /**
     * return the pager
     *
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * return Back Url
     *
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index', ['_secure' => true, '_nosid' => true]);
    }

    /**
     * Return order view link
     *
     * @param string $order
     * @return String
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('*/*/view', ['order_id' => $order->getId(), '_secure' => true, '_nosid' => true]);
    }

    /**
     * @return mixed
     */
    public function getStatusArray(){
        return $this->grid->getStates();
    }

    /**
     * @return mixed
     */
    public function getStates(){
        return $this->vorders->getStates();
    }

    /**
     * @param $price
     * @param bool $includeContainer
     * @param int $precision
     * @param null $scope
     * @param $currency
     * @return float
     */
    public function formatCurrency(
        $price,
        $includeContainer = false,
        $precision = 2,
        $scope = null,
        $currency
    ){
        return $this->_priceCurrency->format(
            $price,
            $includeContainer,
            $precision,
            $scope,
            $currency
        );
    }

}
