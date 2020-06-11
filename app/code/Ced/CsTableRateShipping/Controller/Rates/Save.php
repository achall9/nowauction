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
 * @package     Ced_CsTableRateShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTableRateShipping\Controller\Rates;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 * @package Ced\CsTableRateShipping\Controller\Rates
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsTableRateShipping\Model\TablerateFactory
     */
    protected $tablerateFactory;

    /**
     * @var \Ced\CsMultiShipping\Helper\Data
     */
    protected $multishippingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Save constructor.
     * @param \Ced\CsTableRateShipping\Model\TablerateFactory $tablerateFactory
     * @param \Ced\CsMultiShipping\Helper\Data $multishippingHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
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
        \Ced\CsTableRateShipping\Model\TablerateFactory $tablerateFactory,
        \Ced\CsMultiShipping\Helper\Data $multishippingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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

        $this->tablerateFactory = $tablerateFactory;
        $this->multishippingHelper = $multishippingHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Default vendor dashboard page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPostValue();
            $vendor_id = $this->_getSession()->getVendorId();
            $website_id = $this->storeManager->getStore()->getWebsiteId();
            $dest_country_id = $data['groups']['address']['country_id'];
        }

        //validate destination region
        if (!empty($data['groups']['address']['region_id'])) {
            $dest_region_id = $data['groups']['address']['region_id'];
        }

        elseif ($data['groups']['address']['region'] == '*' || $data['groups']['address']['region'] == '') {
            $dest_region_id = 0;
        } else {
            $this->messageManager->addErrorMessage(__('Please enter valid region code.'));
            $this->_redirect('cstablerateshipping/rates/add');
            return;
        }

        //validate zip code
        if ($data['groups']['address']['postcode'] == '*' || $data['groups']['address']['postcode'] == '') {
            $dest_zip_code = '*';
        } else {
            $dest_zip_code = $data['groups']['address']['postcode'];
        }

        $condition_name = $data['groups']['address']['condition_name'];

        //validate condition value
        if ($data['groups']['address']['condition_value'] === false) {
            $this->messageManager->addErrorMessage(__('Please enter valid condition value.'));
            $this->_redirect('cstablerateshipping/rates/add');
            return;
        }
        $condition_value = $data['groups']['address']['condition_value'];

        //validate price
        if (isset($data['groups']['address']['price'])) {
            $price = $data['groups']['address']['price'];
        } else {
            $this->messageManager->addErrorMessage(__('Please enter price.'));
            $this->_redirect('cstablerateshipping/rates/add');
            return;
        }

        $model = $this->tablerateFactory->create();
        $id = $this->getRequest()->getParam('id');

        if (!isset($id)) {
            try {
                $model->setData('vendor_id', $vendor_id)
                    ->setData('website_id', $website_id)
                    ->setData('dest_country_id', $dest_country_id)
                    ->setData('dest_region_id', $dest_region_id)
                    ->setData('dest_zip', $dest_zip_code)
                    ->setData('condition_name', $condition_name)
                    ->setData('condition_value', $condition_value)
                    ->setData('price', $price)->save();
                $this->messageManager->addSuccessMessage(__('New Rate has been saved.'));
                $this->_redirect('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Cannot enter duplicate data..'));
                $this->_redirect('cstablerateshipping/rates/add');
                return;
            }
        } else {
            $model->load($id);
            try {
                $model->setData('vendor_id', $vendor_id)
                    ->setData('website_id', $website_id)
                    ->setData('dest_country_id', $dest_country_id)
                    ->setData('dest_region_id', $dest_region_id)
                    ->setData('dest_zip', $dest_zip_code)
                    ->setData('condition_name', $condition_name)
                    ->setData('condition_value', $condition_value)
                    ->setData('price', $price)->save();
                $this->messageManager->addSuccessMessage(__('Rate has been saved.'));
                $this->_redirect('*/*/index');
            } catch (Exception $e) {
                echo $e->getMessage();
                $this->messageManager->addErrorMessage(__('Cannot enter duplicate data..'));
                $this->_redirect('cstablerateshipping/rates/add', array('id' => $id));
                return;
            }
        }


        $section = $this->getRequest()->getParam('section', '');
        $groups = $this->getRequest()->getPost('groups', array());

        if (strlen($section) > 0 && $this->_getSession()->getData('vendor_id') && count($groups) > 0) {
            $vendor_id = (int)$this->_getSession()->getData('vendor_id');
            try {
                $this->multishippingHelper->saveShippingData($section, $groups, $vendor_id);
                $this->messageManager->addSuccessMessage(__('The Shipping Methods has been saved.'));
                $this->_redirect('*/*/index');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/index');
                return;
            }
        }
        $this->_redirect('*/*/index');
    }
}
