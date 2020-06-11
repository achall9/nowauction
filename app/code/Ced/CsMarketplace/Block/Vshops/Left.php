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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vshops;

/**
 * Class Left
 * @package Ced\CsMarketplace\Block\Vshops
 */
class Left extends \Magento\Catalog\Block\Navigation
{

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $_vproductsFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    /**
     * Left constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_vproductsFactory = $vproductsFactory;
        parent::__construct($context, $categoryFactory, $productCollectionFactory, $layerResolver, $httpContext, $catalogCategory, $registry, $flatState, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                'cache_lifetime' => 0,
                'cache_tags' => [],
            ]
        );
    }

    /**
     * @param $category
     * @param bool $flag
     * @param int $lvl
     * @return string
     */
    public function getCategoriesHtml($category, $flag = false, $lvl = 0)
    {
        $html = '';
        if (is_numeric($category)) {

            $_category = $this->loadCategory($category);
            $_categories = $_category->getChildrenCategories();
        } elseif ($category && $category->getId()) {
            $_categories = $category->getChildrenCategories();
        }
        $category_filter = $this->getRequest()->getParam('cat-fil');

        $curVendorId = $this->_registry->registry('current_vendor')->getEntityId();


        $cat_fil = array();
        if (isset($category_filter))
            $cat_fil = explode(',', $category_filter);
        if (count($_categories) > 0) {
            $html = '<ul class="level-' . $lvl . ' vshop-left-cat-filter root-category root-category-wrapper">';
            $level = $lvl + 1;
            foreach ($_categories as $value) {
                if ($count = $this->_vproductsFactory->create()->getProductCountcategory($curVendorId, $value->getId(), \Ced\CsMarketplace\Model\Vproducts::AREA_FRONTEND)) {
                    $html .= '<li class="tree-node">';
                    if ($this->getCategoriesHtml($value->getId(), true, $level)) {
                        $html .= '<img class="tree-ec-icon tree-elbow-plus" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                    } else {
                        //$html .= '<img class="tree-ec-icon tree-elbow-line" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                        $html .= '<img class="tree-ec-icon tree-elbow-end" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';

                    }
                    $html .= '<input class="cat-fil" onchange="filterProductsByCategory(this)" type="checkbox" name="cat-fil" data-uncheckurl="' . $this->getUncheckFilterUrl($value->getId()) . '" value="' . $this->getCheckFilterUrl($value->getId()) . '" ';
                    if (in_array($value->getId(), $cat_fil))
                        $html .= 'checked="checked"';
                    $html .= '>';
                    $label = $value->getName() . " (" . $count . ")";
                    $html .= '<label>' . $label . '</label>';
                    $html .= $this->getCategoriesHtml($value->getId(), true, $level);
                    $html .= '</li>';
                }
            }
            $html .= '</ul>';


        }
        return $html;
    }

    /**
     * @param $category_id
     * @return string
     */
    public function getCheckFilterUrl($category_id)
    {
        $urlParams = array('_current' => true, '_escape' => true, '_use_rewrite' => true);

        $category_filter = $this->getRequest()->getParam('cat-fil');

        if (isset($category_filter)) {
            $cat_fil = explode(',', $category_filter);
            if (!in_array($category_id, $cat_fil)) {
                $urlParams['_query'] = array('cat-fil' => $category_filter . ',' . $category_id);
            }
        } else
            $urlParams['_query'] = array('cat-fil' => $category_id);

        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * @param $category_id
     * @return string
     */
    public function getUncheckFilterUrl($category_id)
    {
        $urlParams = array('_current' => true, '_escape' => true, '_use_rewrite' => true);

        $category_filter = $this->getRequest()->getParam('cat-fil');

        if (isset($category_filter)) {
            $cat_fil = explode(',', $category_filter);
            if (in_array($category_id, $cat_fil)) {
                $cat_fil = $this->remove_array_item($cat_fil, $category_id);
                if (!count($cat_fil))
                    return trim($this->getBaseUrl(), '/') . rtrim($this->getRequest()->getOriginalPathInfo(), '/');
                elseif (count($cat_fil) > 0)
                    $urlParams['_query'] = array('cat-fil' => implode(',', $cat_fil));
            }
        }
        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * @param $array
     * @param $item
     * @return mixed
     */
    public function remove_array_item($array, $item)
    {
        $index = array_search($item, $array);
        if ($index !== false) {
            unset($array[$index]);
        }

        return $array;
    }

    /**
     * @param $id
     * @return $this
     */
    public function loadCategory($id)
    {
        return $this->_categoryInstance->load($id);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_storeManager->getStore(null);
    }


}
