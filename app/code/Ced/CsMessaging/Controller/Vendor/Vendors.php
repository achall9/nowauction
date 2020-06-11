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
 * @package     Ced_CsMessaging
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Controller\Vendor;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;

class Vendors extends \Magento\Framework\App\Action\Action
{

    public $resultJsonFactory;
    public $urlFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory,
        \Ced\CsMarketplace\Model\Session $vendorSession,
        \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollFactory
    )
    {
        parent::__construct($context);
        $this->urlFactory = $urlFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->customerSession = $customerSession;
        $this->vendorSession = $vendorSession;
        $this->vcustomerMessageCollFactory = $vcustomerMessageCollFactory;
    }


    /**
     * Default vendor dashboard page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $currentVendorId = $this->vendorSession->getVendorId();
        $currentCustomerId = $this->customerSession->getCustomerId();

        $vcustomerCollection = $this->vcustomerMessageCollFactory->create();
        if (!empty($vcustomerCollection))
        {
            foreach ($vcustomerCollection as $message)
            {
                if($message->getCustomerId()== $currentCustomerId && $message->getSender()!='customer')
                {
                    $message->setReceiverStatus(\Ced\CsMessaging\Helper\Data::STATUS_READ);
                }
                $message->save();
            }
        }

        $vendors = $this->vendorCollectionFactory->create();
        $vendors->addAttributeToSelect('public_name','entity_id')
                ->addFieldToFilter('entity_id',['neq'=>$currentVendorId]);
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($vendors->toArray());
        return $resultJson;
    }
}
