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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block\Transaction\Grid\Renderer;

/**
 * Class Action
 * @package Ced\CsTransaction\Block\Transaction\Grid\Renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Ced\CsTransaction\Model\Items
     */
    protected $items;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * Action constructor.
     * @param \Ced\CsTransaction\Model\Items $items
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsTransaction\Model\Items $items,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        $this->items = $items;
        $this->sessionFactory = $sessionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $model = $this->items->load($row->getId());
        if ($model->getIsRequested() == 1 && $model->getItemPaymentState() == \Ced\CsTransaction\Model\Items::STATE_READY_TO_PAY) {
            $html .= __('Requested');
        } elseif ($model->getItemPaymentState() == \Ced\CsTransaction\Model\Items::STATE_PAID) {
            $html .= __('Paid');
        } elseif ($model->getQtyOrdered() == $model->getQtyRefunded()) {
            $html .= __('Cancelled');
        } elseif ($model->getQtyOrdered() == $model->getQtyReadyToPay() + $model->getQtyRefunded()) {

            $url = $this->getUrl('cstransaction/vpayments/requestpost', array('payment_request' => $row->getId()));
            $html .= $this->getRequestButtonHtml($url);
        } else {

            $html .= __('Not Allowed');
        }
        return $html;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getRequestButtonHtml($url = '')
    {
        return '<input class="button scalable save" style="cursor: pointer; background: #ffac47 url("images/btn_bg.gif") repeat-x scroll 0 100%;border-color: #ed6502 #a04300 #a04300 #ed6502;    border-style: solid;    border-width: 1px;    color: #fff;    cursor: pointer;    font: bold 12px arial,helvetica,sans-serif;    padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;" type="button" onclick="setLocation(\'' . $url . '\')" value="Request">';
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->sessionFactory->create()->getVendorId();
    }
}
