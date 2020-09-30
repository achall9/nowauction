<?php
namespace Ced\CustomizeAuction\Plugin\SalesRule\Model;

use Magento\Framework\Session\SessionManager;

class RulesApplier
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    private $rules;

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $rulesFactory
    ) {
        $this->ruleCollection = $rulesFactory;
    }

    public function aroundApplyRules(
        \Magento\SalesRule\Model\RulesApplier $subject,
        \Closure $proceed,
        $item,
        $rules,
        $skipValidation,
        $couponCode
    ) {
        $rules = $this->ruleCollection->create()->addFieldToFilter("rule_id", ["eq"=>0]);
        $result = $proceed($item, $rules, $skipValidation, $couponCode);
        return $result;
    }
}