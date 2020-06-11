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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Controller\Downloadable\Product\Edit;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Downloadable\Helper\Download as DownloadHelper;

/**
 * Class Link
 * @package Ced\CsProduct\Controller\Downloadable\Product\Edit
 */
class Link extends \Ced\CsProduct\Controller\Vproducts
{
    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    protected $linkFactory;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Magento\Downloadable\Helper\Download
     */
    protected $downloadHelper;

    /**
     * Link constructor.
     * @param \Magento\Downloadable\Model\LinkFactory $linkFactory
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Magento\Downloadable\Helper\Download $downloadHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Request\Http $http
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     */
    public function __construct(
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        \Magento\Downloadable\Helper\File $fileHelper,
        DownloadHelper $downloadHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $http,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
    )
    {
        $this->linkFactory = $linkFactory;
        $this->fileHelper = $fileHelper;
        $this->downloadHelper = $downloadHelper;
        parent::__construct(
            $scopeConfig,
            $http,
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor,
            $storeManager,
            $productFactory,
            $vproductsFactory,
            $type
        );
    }

    /**
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _createLink()
    {
        return $this->linkFactory->create();
    }

    /**
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _getLink()
    {
        return $this->linkFactory->create();
    }

    /**
     * Download process
     *
     * @param string $resource
     * @param string $resourceType
     * @return void
     */
    protected function _processDownload($resource, $resourceType)
    {
        /* @var $helper \Magento\Downloadable\Helper\Download */
        $helper = $this->downloadHelper;
        $helper->setResource($resource, $resourceType);

        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();

        $this->getResponse()->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if ($fileSize = $helper->getFileSize()) {
            $this->getResponse()->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()
                ->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        $helper->output();
    }

    /**
     * Download link action
     *
     * @return void
     */
    public function execute()
    {
        $linkId = $this->getRequest()->getParam('id', 0);
        $type = $this->getRequest()->getParam('type', 0);
        /** @var \Magento\Downloadable\Model\Link $link */
        $link = $this->_createLink()->load($linkId);
        if ($link->getId()) {
            $resource = '';
            $resourceType = '';
            if ($type == 'link') {
                if ($link->getLinkType() == DownloadHelper::LINK_TYPE_URL) {
                    $resource = $link->getLinkUrl();
                    $resourceType = DownloadHelper::LINK_TYPE_URL;
                } elseif ($link->getLinkType() == DownloadHelper::LINK_TYPE_FILE) {
                    $resource = $this->fileHelper->getFilePath(
                        $this->_getLink()->getBasePath(),
                        $link->getLinkFile()
                    );
                    $resourceType = DownloadHelper::LINK_TYPE_FILE;
                }
            } else {
                if ($link->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                    $resource = $link->getSampleUrl();
                    $resourceType = DownloadHelper::LINK_TYPE_URL;
                } elseif ($link->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                    $resource = $this->fileHelper->getFilePath(
                        $this->_getLink()->getBaseSamplePath(),
                        $link->getSampleFile()
                    );
                    $resourceType = DownloadHelper::LINK_TYPE_FILE;
                }
            }
            try {
                $this->_processDownload($resource, $resourceType);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while getting the requested content.'));
            }
        }
    }
}
