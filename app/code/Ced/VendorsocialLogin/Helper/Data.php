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
  * @package     VendorsocialLogin
  * @author      CedCommerce Core Team <connect@cedcommerce.com>
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://cedcommerce.com/license-agreement.txt
  */

/**
 * VendorsocialLogin 	Data Helper
 *
 * @category   	Ced
 * @package    	Ced_VendorsocialLogin
 * @author		CedCommerce Magento Core Team <connect@cedcommerce.com>
 */
namespace Ced\VendorsocialLogin\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper

{

    /**

     * @var \Magento\Framework\App\State

     */

    protected $_appState;

    /**

     * @param \Magento\Framework\App\Helper\Context $context

     * @param \Magento\Framework\App\State $appState

     */

    public function __construct(

        \Magento\Framework\App\Helper\Context $context,

        \Magento\Framework\App\State $appState)

    {

        $this->_appState = $appState;

        parent::__construct($context);

    }

    public function log1($message, $level = \Zend_Log::DEBUG, $loggerKey = \Magento\Framework\Logger::LOGGER_SYSTEM)

    {

        if($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {

            $this->_logger->log($message, $level, $loggerKey);

        }

    }

}