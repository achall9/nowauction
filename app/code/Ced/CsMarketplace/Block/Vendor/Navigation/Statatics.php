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

namespace Ced\CsMarketplace\Block\Vendor\Navigation;
 
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

class Statatics extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
	protected $_vendor;
	protected $_totalattr = 0;
	protected $_savedattr = 0;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $setCollection;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollection;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\AttributeFactory
     */
    protected $vendorAttribute;

    /**
     * Statatics constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollection
     * @param \Ced\CsMarketplace\Model\Vendor\AttributeFactory $vendorAttribute
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollection,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $vendorAttribute
    )
    {
        $this->vendorFactory = $vendorFactory;
        $this->setCollection = $setCollection;
        $this->groupCollection = $groupCollection;
        $this->vendorAttribute = $vendorAttribute;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }
	
	public function getTotalAttr() {
		return $this->_totalattr;
	}
	
	public function getSavedAttr() {
		return $this->_savedattr;
	}
	
	/**
     * Preparing collection of vendor attribute group vise
     *
     */
	public function getVendorAttributeInfo() {
		$this->_totalattr = 0;
		$this->_savedattr = 0;
		$this->_vendor = $this->vendorFactory->create();
		$entityTypeId  = $this->_vendor->getEntityTypeId();
		$setIds = $this->setCollection->create()
				->setEntityTypeFilter($entityTypeId)->getAllIds();
				
		$groupCollection =  $this->groupCollection->create();
		if(count($setIds) > 0) {
			$groupCollection->addFieldToFilter('attribute_set_id',array('in'=>$setIds));
		}
		$groupCollection->setSortOrder()->load();
				
		$vendor_info = $this->_vendor->load($this->getVendorId());
		$total = 0;
		$saved = 0;
		foreach($groupCollection as $group){
			$attributes = $this->_vendor->getAttributes($group->getId(), true);
			if (count($attributes)==0) {
				continue;
			}
			
			foreach ($attributes as $attr){
				$attribute = $this->vendorAttribute->create()->setStoreId(0)->load($attr->getid());
				if(!$attribute->getisVisible()) continue;
				$total += 1;
				if($vendor_info->getData($attr->getAttributeCode())){
					$saved++;
				}
			}
		}
		$this->_totalattr = $total-1; 
		$this->_savedattr = $saved;	
		
		return $groupCollection;
	}
	
}
