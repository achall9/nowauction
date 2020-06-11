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
  * @package   Ced_CsOrder
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsOrder\Block\Order\Shipment\Renderer;

/**
 * Class Totalqty
 * @package Ced\CsOrder\Block\Order\Shipment\Renderer
 */
class Totalqty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Ced\CsOrder\Model\Shipment
     */
    protected $csOrderShipment;

    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $shipment;

    /**
     * Grandtotal constructor.
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param \Ced\CsOrder\Model\Shipment $csOrderShipment
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\Order\Shipment $shipment,
        \Ced\CsOrder\Model\ShipmentFactory $csOrderShipment,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->shipment = $shipment;
        $this->csOrderShipment = $csOrderShipment;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return int|\Magento\Sales\Model\Order\Shipment
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $shipment = $this->shipment->load($row->getEntityId());
        $shipment = $this->csOrderShipment->create()->updateTotalqty($shipment);
        return $shipment;
    }
}

