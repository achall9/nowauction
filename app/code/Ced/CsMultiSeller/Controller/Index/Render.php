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
 * @package     Ced_CsMultiSeller
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiSeller\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Render
 * @package Ced\CsMultiSeller\Controller\Index
 */
class Render extends AbstractAction
{
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $translate;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $resolver;

    /**
     * Render constructor.
     * @param \Magento\Framework\TranslateInterface $translate
     * @param \Magento\Framework\Locale\ResolverInterface $resolver
     * @param Context $context
     * @param UiComponentFactory $factory
     */
    public function __construct(
        \Magento\Framework\TranslateInterface $translate,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        Context $context,
        UiComponentFactory $factory
    )
    {
        $this->translate = $translate;
        $this->resolver = $resolver;
        parent::__construct($context, $factory);
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->resolver->setLocale('en_US');
            $this->translate->setLocale('en_US');
            $this->translate->loadData('frontend');
        } catch (StoreIsInactiveException $e) {
            $error = __('Requested store is inactive');
        } catch (NoSuchEntityException $e) {
            $error = __('Requested store is not found');
        }

        if ($this->_request->getParam('namespace') === null) {
            $this->_redirect('admin/noroute');
            return;
        }

        $component = $this->factory->create($this->_request->getParam('namespace'));
        $this->prepareComponent($component);
        $this->_response->appendBody((string)$component->render());
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}