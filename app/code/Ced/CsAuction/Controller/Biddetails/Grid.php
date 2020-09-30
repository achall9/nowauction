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
 * @package     Ced_CsAuction
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Controller\Biddetails;


class Grid extends \Ced\CsMarketplace\Controller\Vendor
{      
	/**
	 * @return \Magento\Framework\View\Result\Layout
	 */
	public function execute()
	{
		$resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__(' heading '));

        $block = $resultPage->getLayout()
                ->createBlock('Ced\CsAuction\Block\AddAuction\Edit\Tab\Biddetails')
                ->toHtml();
        $this->getResponse()->setBody($block);
	}
}