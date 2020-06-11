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
 * @package     Ced_CsTransaction
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block\Adminhtml\Vpayments\Grid\Renderer;

/**
 * Class Orderdesc
 * @package Ced\CsTransaction\Block\Adminhtml\Vpayments\Grid\Renderer
 */
class Orderdesc extends \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\Orderdesc
{
    /**
     * @var bool
     */
    protected $_frontend = false;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $_currencyInterface;

    /**
     * @var \Ced\CsTransaction\Model\Items
     */
    protected $_vtorders;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $orderHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * Orderdesc constructor.
     * @param \Ced\CsTransaction\Model\Items $vtorders
     * @param \Ced\CsOrder\Helper\Data $orderHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsTransaction\Model\Items $vtorders,
        \Ced\CsOrder\Helper\Data $orderHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Locale\Currency $localeCurrency,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        array $data = []
    )
    {
        $this->_vtorders = $vtorders;
        $this->orderHelper = $orderHelper;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $localeCurrency, $design, $orderFactory, $vordersFactory, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return bool|string
     * @throws \Zend_Currency_Exception
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($this->orderHelper->isActive()) {

            $amountDesc = $row->getItem_wise_amount_desc();
            $html = '';
            $amountDesc = json_decode($amountDesc, true);
            if (is_array($amountDesc)) {
                foreach ($amountDesc as $incrementId => $amounts) {

                    if (is_array($amounts)) {
                        foreach ($amounts as $item_id => $baseNetAmount) {
                            if (is_array($baseNetAmount))
                                return false;
                            $url = 'javascript:void(0);';
                            $target = "";

                            $amount = $this->_currencyInterface->getCurrency($row->getBaseCurrency())->toCurrency($baseNetAmount);
                            $vorder = $this->orderFactory->create()->load($incrementId);
                            $incrementId = $vorder->getIncrementId();

                            if ($this->_frontend && $vorder && $vorder->getId()) {
                                $url = $this->getUrl("csmarketplace/vorders/view/", array(
                                    'increment_id' => $incrementId
                                ));
                                $target = "target='_blank'";
                                $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . "<a href='" . $url . "' " . $target . " >" . $incrementId . "</a>" . '</label><br/>';
                            } else {
                                $item = $this->_vtorders->load($item_id);
                                $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . $incrementId . ' : ' . $item->getSku() . '</label><br/>';
                            }
                        }
                    }
                }
            } else {
                $amountDesc = $row->getAmountDesc();
                if ($amountDesc != '') {

                    $amountDesc = json_decode($amountDesc, true);
                    if (is_array($amountDesc)) {
                        foreach ($amountDesc as $incrementId => $baseNetAmount) {
                            if (is_array($baseNetAmount))
                                return false;
                            $url = 'javascript:void(0);';
                            $target = "";
                            $amount = $this->_currencyInterface->getCurrency($row->getBaseCurrency())->toCurrency($baseNetAmount);
                            $vorder = $this->orderFactory->create()->load($incrementId);
                            if ($this->_frontend && $vorder && $vorder->getId()) {
                                $url = $this->getUrl("csmarketplace/vorders/view/", array(
                                    'increment_id' => $incrementId
                                ));
                                $target = "target='_blank'";
                                $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . "<a href='" . $url . "' " . $target . " >" . $incrementId . "</a>" . '</label><br/>';
                            } else
                                $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . $incrementId . '</label><br/>';
                        }
                    }
                }
            }
            if ($vendorId = $this->getRequest()->getParam('id')) {
                $html .= $this->getDetails($row);
            }
            if ($vendorId = $this->getRequest()->getParam('payment_id')) {
                $html .= $this->getDetails($row);
            }

            return $html;
        }
        return parent::render($row);
    }

    /**
     * @param $row
     * @return bool|string
     */
    public function getDetails($row)
    {

        $amountDesc = $row->getItem_wise_amount_desc();
        $orderArray = json_decode($amountDesc, true);

        $html = "";
        if ($orderArray) {
            $html .= '<div class="grid" id="order-items_grid">
						<table cellspacing="0" class="data order-tables" style="width:50%; float:right" border="1">
		 
							<col width="100" />
							<col width="40" />
							<col width="100" />
							<col width="80" />
							<thead>
								<tr class="headings" style="background-color: rgb(81, 73, 67); color: white;">';

            $html .= '<th class="no-link"><center>' . __("Order Id") . '</center></th>
										<th class="no-link"><center>' . __("Order Total") . '</center></th>
										<th class="no-link"><center>' . __("Commission Fee") . '</center></th>
										<th class="no-link"><center>' . __("Net Earned") . '</center></th>	
								</tr>
							</thead>
							<tbody>';
            $class = '';
            $trans_sum = 0;
            /* foreach($orderArray as $info){ */
            foreach ($orderArray as $key => $value) {
                $class = ($class == 'odd') ? 'even' : 'odd';
                $html .= '<tr class="' . $class . '">';
                foreach ($value as $key1 => $value1) {

                    $html .= '<td><center>' . $this->getVendorOrderId($key1) . '</center></td>
												<td><center>' . $this->formatPrice($value1) . '</center></td>
												<td><center>' . $this->formatPrice($this->getVendorItemCommission($key1, $value1)) . '</center></td>
												<td><center>' . $this->formatPrice(($value1 - $this->getVendorItemCommission($key1, $value1) + $row->getTotalShippingAmount() - $row->getBaseFee())) . '</center></td></tr>';
                }
            }

            $html .= '</tbody></table><div><div><div><div>';
            // $html.='<span><h3>Total:'.$trans_sum.'</h3></span>';
            return $html;
            // $html.='<h3>'.__('Service Tax').' : '.$orderArray['service_tax_amount'].'</h3>';
        }
        return false;
    }

    /**
     * @param $orderid
     * @param $value1
     * @return mixed
     */
    public function getVendorItemCommission($orderid, $value1)
    {
        $vorder = $this->_vtorders->load($orderid);

        return $vorder->getItemCommission();
    }

    /**
     * @param $orderid
     * @return int
     */
    public function getVendorOrderId($orderid)
    {
        $vorder = $this->_vtorders->load($orderid);
        $order_increment_id = 0;
        foreach ($vorder as $key => $value) {
            $order_increment_id = $value ['order_increment_id'];
        }
        return $order_increment_id;
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        $formattedPrice = $this->pricingHelper->currency($price, true, false);
        return $formattedPrice;
    }
}
