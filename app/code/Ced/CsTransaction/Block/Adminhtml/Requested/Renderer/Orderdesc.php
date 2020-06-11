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

namespace Ced\CsTransaction\Block\Adminhtml\Requested\Renderer;

/**
 * Class Orderdesc
 * @package Ced\CsTransaction\Block\Adminhtml\Requested\Renderer
 */
class Orderdesc extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var bool
     */
    protected $_frontend = false;

    /**
     * Orderdesc constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $orderIds = $row->getOrderId();
        $html = '';
        if ($orderIds != '') {
            $orderIds = explode(',', $orderIds);
            foreach ($orderIds as $orderId) {
                $url = 'javascript:void(0);';
                $target = "";
                $html .= '<label for="order_id_' . $orderId . '"><b>Order# </b>' . $orderId . '</label><br/>';
            }
        }
        return $html;
    }
}