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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 

namespace Ced\CsMarketplace\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Helper\Data;
use Magento\Framework\Module\Manager;

class Downloadtc extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public $helper;

    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\App\Filesystem\DirectoryList $directory,
        PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Manager $moduleManager,
        Data $datahelper
    ) {
        $this->helper = $datahelper;
        $this->directory = $directory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);

    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $tc_content = $this->helper->getStoreConfig("ced_csmarketplace/general/tc_content",null);
        $path = $this->directory->getRoot().'/pub/media/t&c/';
        if (!file_exists($path))
        {
            mkdir($path, 0777, true);
        }
        $file = 'terms&conditions.doc';
        $upload = fopen($path.$file, 'w');
        fwrite($upload, $tc_content);
        fclose($upload);
        $tcfile = $this->_storeManager->getStore()->getBaseUrl().'/pub/media/t&c/'.$file;
        return $resultRedirect->setPath($tcfile);
    }

}
