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
namespace Ced\CsImportExport\Controller\Import;

class Unlink extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * Image Uploading
     *
     * @return null
     */
    public function execute()
    {
        $vendorId = $this->session->getVendorId();
        $path=$this->getRequest()->getParam('delete');
        $singlepath=$this->getRequest()->getPost('singlepath');
        if($vendorId)
        {
            if(!empty($path) && count($path)>0) {
                try{
                    foreach($path as $_path)
                    {
                        unlink($_path);

                    }
                    $this->messageManager->addSuccessMessage('Image Has Been Deleted Successfuly');
                    $this->_redirect('csimportexport/import/image');

                }
                catch(\Exception $e)
                {
                    echo __($e->getMessage());
                }
            }
            else
            {
                try{
                    unlink($singlepath);
                }
                catch(\Exception $e)
                {
                    echo __($e->getMessage());
                }
            }
        }
    }
}
