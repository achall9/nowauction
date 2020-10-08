<?php 
namespace Ced\CustomizeAuction\Controller\Methods;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMultiShipping\Helper\Data
     */
    protected $csmultishippingHelper;
    protected $resourceConnection;

    /**
     * Index constructor.
     * @param \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->csmultishippingHelper = $csmultishippingHelper;
        $this->resourceConnection = $resourceConnection;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Default vendor dashboard page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if(!$this->_getSession()->getVendorId()) {
            return; 
        }
        $vendorId = $this->_getSession()->getVendorId();
        $params = $this->getRequest()->getParams();
        $jsonEncode = json_encode($params);
        $resource = $this->resourceConnection;
        $connection = $resource->getConnection();
    
        $winTable = $resource->getTableName('vendor_shippingmethods');

        $getData = "SELECT count(*) as total FROM " . $winTable." WHERE vendor_id = ".$vendorId;
        $results = $connection->fetchAll($getData);

        if(count($results) >= 1)
        {
            $sql = "Update " . $winTable." Set shipping_method = '".$jsonEncode."' WHERE vendor_id = ".$vendorId;
            $connection->query($sql);
        } else {
            $sql = "INSERT INTO " . $winTable." (vendor_id, shipping_method) VALUES (".$vendorId.", '".$jsonEncode."')";
            $connection->query($sql);
        }

       
        /* if(!$this->_getSession()->getVendorId()) {
            return; 
        }
        if(!$this->csmultishippingHelper->isEnabled()) {
            $this->_redirect('csmarketplace/vendor/view');
            return;
        }
        $section = $this->getRequest()->getParam('section', '');
        $groups = $this->getRequest()->getPost('groups', array());

        if(strlen($section) > 0 && $this->_getSession()->getData('vendor_id') && count($groups)>0) {
            $vendor_id = (int)$this->_getSession()->getData('vendor_id');
            try {
                $this->csmultishippingHelper->saveShippingData($section, $groups, $vendor_id);
                $this->messageManager->addSuccessMessage(__('The Shipping Methods has been saved.'));
                $this->_redirect('*//**//*view');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('/**//**//*view');
                return;
            }
        } */
        $this->_redirect('*/*/view');   
    }
}
