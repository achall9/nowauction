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
  * @package   Ced_CsOrder
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsOrder\Controller\Creditmemo;
 
class Grid extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * Grid action
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody($this->resultPageFactory->create(true)->getLayout()->createBlock('Ced\CsOrder\Block\ListCreditmemo\Grid')->toHtml());
    }
}
