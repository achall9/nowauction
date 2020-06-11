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
namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer;

class Orderdesc extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
     * @var \Magento\Framework\View\DesignInterface
     */
	protected $design;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
	protected $orderFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
	protected $vordersFactory;

    /**
     * Orderdesc constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param array $data
     */
	public function __construct(\Magento\Backend\Block\Context $context,
    	\Magento\Framework\Locale\Currency $localeCurrency,
		\Magento\Framework\View\DesignInterface $design,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		\Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
		array $data = []
    )
	{
   		$this->_currencyInterface = $localeCurrency;
   		$this->design = $design;
   		$this->orderFactory = $orderFactory;
   		$this->vordersFactory = $vordersFactory;
		parent::__construct($context, $data);
	}
	
	public function render(\Magento\Framework\DataObject $row)
	{
		
		$amountDesc=$row->getAmountDesc();
		$html='';
		$area = $this->design->getArea();
		if($amountDesc!=''){
			$amountDesc=json_decode($amountDesc,true);
			foreach ($amountDesc as $incrementId=>$baseNetAmount){
					$url = 'javascript:void(0);';
					$target = "";
					$amount = $this->_currencyInterface->getCurrency($row->getBaseCurrency())->toCurrency($baseNetAmount);
					$vorder = $this->orderFactory->create()->loadByIncrementId($incrementId);
					 
					$orderId = $this->vordersFactory->create()->load($incrementId,'order_id')->getId(); 
					
					if ($area!='adminhtml' && $vorder && $vorder->getId()) {
						$url =  $this->getUrl("csmarketplace/vorders/view/",array('order_id'=>$orderId));
						$target = "target='_blank'";
						$html .='<label for="order_id_'.$incrementId.'"><b>Order# </b>'."<a href='". $url . "' ".$target." >".$incrementId."</a>".'</label>, <b>Net Earned </b>'.$amount.'<br/>';
					}
					else 
						$html .='<label for="order_id_'.$incrementId.'"><b>Order# </b>'.$incrementId.'</label>, <b>Amount </b>'.$amount.'<br/>';
			}
		}
		
		return $html;
	}
 
}