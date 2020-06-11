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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
 
namespace Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer;

class Vendorname extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	/**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Vendorname constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = []
    )
    {
        $this->design = $design;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $data);
    }
	
	/**
	* Return the Vendor Link
	*
	*/
	public function render(\Magento\Framework\DataObject $row)
    {
		$area = $this->design->getArea();
    	if($row->getVendorId()!=''){
			$vendor = $this->vendorFactory->create()->load($row->getVendorId());
			$url = 'javascript:void(0);';
			$target = "";
			if ($area=='adminhtml') {
				$url =  $this->getUrl("csmarketplace/vendor/edit/", array('vendor_id' => $vendor->getId()));
				$target = "target='_blank'";
			}
		  return "<a href='". $url . "' ".$target." >".$vendor->getName()."</a>";		  
		}
		return '';
   	}
}