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
 * @package     Ced_CsTableRateShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTableRateShipping\Controller\Export;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ExportTablerates
 * @package Ced\CsTableRateShipping\Controller\Export
 */
class ExportTablerates extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $_allowedResource = true;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Ced\CsMultiShipping\Helper\Data
     */
    protected $multishippingHelper;

    /**
     * @var \Ced\CsTableRateShipping\Helper\Data
     */
    protected $tablerateHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VsettingsFactory
     */
    protected $vsettingsFactory;

    /**
     * ExportTablerates constructor.
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Ced\CsMultiShipping\Helper\Data $multishippingHelper
     * @param \Ced\CsTableRateShipping\Helper\Data $tablerateHelper
     * @param \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
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
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Ced\CsMultiShipping\Helper\Data $multishippingHelper,
        \Ced\CsTableRateShipping\Helper\Data $tablerateHelper,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
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

        $this->session = $customerSession;
        $this->_fileFactory = $fileFactory;
        $this->multishippingHelper = $multishippingHelper;
        $this->tablerateHelper = $tablerateHelper;
        $this->vsettingsFactory = $vsettingsFactory;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * Export shipping table rates in csv format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        if (!$this->multishippingHelper->isEnabled() || !$this->tablerateHelper->isEnabled()) {
            $this->_redirect('csmarketplace/vendor/index');
            return;
        }
        $fileName = 'tablerates.csv';
        $gridBlock = $this->_view->getLayout()->createBlock(
        //'Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid'
            'Ced\CsTableRateShipping\Block\Adminhtml\Carrier\Tablerate\Grid'
        );

        $websiteId = $this->getRequest()->getParam('website');
        $vsetting_model = $this->vsettingsFactory->create();
        $conditionName = $vsetting_model->getCollection()->addFieldToFilter('vendor_id', $this->getVendorId())->getData();

        foreach ($conditionName as $key => $value) {

            if ($value['key'] == 'shipping/tablerate/condition') {
                $condition = $value['value'];
            }

        }
        if (empty($condition)) {
            $this->messageManager->addErrorMessage(__('You have to select and save the condition name first.'));
            $this->_redirect('csmultishipping/methods/view');
            return;
        }

        $gridBlock->setWebsiteId($websiteId)->setConditionName($condition);
        $content = $gridBlock->getCsvFile();
        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }

    /**
     * @return Session
     */
    public function _getSession()
    {
        return $this->session;
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->sessionFactory->create()->getVendorId();
    }
}

