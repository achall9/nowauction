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
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsImportExport\Controller\Export;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Uploadimage
 * @package Ced\CsImportExport\Controller\Export
 */
class Uploadimage extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Uploadimage constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
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
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
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

        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * Image Uploading
     *
     * @return null
     */
    public function execute()
    {
        $vendor = $this->getRequest()->getParam('vendor_id');
        $path = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        $path = $path->getAbsolutePath('import/' . $vendor . '/');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        if ($vendor) {
            if (!empty($_FILES['file_upload']['name'])) {
                foreach ($_FILES['file_upload']['name'] as $key => $image) {
                    if (!empty($image)) {
                        try {
                            $broken = str_split($image, 1);
                            $uploader = $this->uploaderFactory->create(
                                array(
                                    'fileId' => "file_upload[{$key}]",
                                )
                            );
                            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'png'));
                            $uploader->setAllowRenameFiles(false);
                            $uploader->setFilesDispersion(true);
                            $uploader->save($path);
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__($e->getMessage()));
                        }
                    }
                }
            }
            $this->messageManager->addSuccessMessage(__('The Images Has Been Successfully Uploaded.'));
            $this->_redirect('*/*/image');
        } else {
            $this->messageManager->addErrorMessage(__('Error Occured While Uploading Image.'));
            $this->_redirect('*/*/image');

        }

    }
}