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

class Unlink extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * Image Uploading
     *
     * @return null
     */
    public function execute()
    {

        $vendor = $this->getRequest()->getParam('vendor_id');
        $path = $this->getRequest()->getParam('delete');

        $singlepath = $this->getRequest()->getPost('singlepath');

        if (!empty($path)) {
            foreach ($path as $k) {
                try {
                    unlink($k);
                } catch (\Exception $e) {

                }
            }

            $this->_redirect('*/import/image');
        } else {
            unlink($singlepath);
        }
    }
}
