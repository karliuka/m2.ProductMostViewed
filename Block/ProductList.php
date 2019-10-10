<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 *
 * See COPYING.txt for license details.
 */
namespace Faonni\ProductMostViewed\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Registry;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Faonni\ProductMostViewed\Model\ResourceModel\Reports\Product\CollectionFactory;

/**
 * Product Most Viewed Block
 *
 * @method int getNumProducts()
 * @method string getInterval()
 * @method string getPeriod()
 */
class ProductList extends AbstractProduct implements IdentityInterface
{
    /**
     * Product Collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $itemCollection;

    /**
     * Catalog Product Visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * Module Manager
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Reports Product Collection Factory
     *
     * @var \Faonni\ProductMostViewed\Model\ResourceModel\Reports\Product\CollectionFactory
     */
    protected $productsFactory;

    /**
     * Initialize Block
     *
     * @param Context $context
     * @param Visibility $catalogProductVisibility
     * @param ModuleManager $moduleManager
     * @param CollectionFactory $productsFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Visibility $catalogProductVisibility,
        ModuleManager $moduleManager,
        CollectionFactory $productsFactory,
        array $data = []
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->moduleManager = $moduleManager;
        $this->productsFactory = $productsFactory;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Prepare Items Data
     *
     * @return \Faonni\ProductMostViewed\Block\ProductList
     */
    protected function _prepareData()
    {
        list($from, $to) = $this->getFromTo();
        $this->itemCollection = $this->productsFactory->create()
            ->addAttributeToSelect('*')
            ->addViewsCount($from, $to)
            ->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->itemCollection);
        }

        $this->itemCollection->setVisibility(
            $this->catalogProductVisibility->getVisibleInCatalogIds()
        );

        if (null !== $this->getCurrentCategory()) {
            $this->itemCollection->addCategoryFilter($this->getCurrentCategory());
        }

        $numProducts = $this->getNumProducts() ?: 6;

        $this->itemCollection->setPage(1, $numProducts);
        $this->itemCollection->load();

        foreach ($this->itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * Retrieve Current Category Model Object
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData(
                'current_category',
                $this->_coreRegistry->registry('current_category')
            );
        }
        return $this->getData('current_category');
    }

    /**
     * Before Rendering Html Process
     *
     * @return \Faonni\ProductMostViewed\Block\ProductList
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Items Collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getItems()
    {
        return $this->itemCollection;
    }

    /**
     * Retrieve Identifiers For Produced Content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            $identities+= (array)$item->getIdentities();
        }
        return $identities;
    }

    /**
     * Retrieve From To Interval
     *
     * @return array
     */
    public function getFromTo()
    {
        $from = '';
        $to = '';
        $interval = (int)$this->getInterval();

        if ($interval > 0) {
            $period = $this->getPeriod();
            $dtTo = new \DateTime();
            $dtFrom = clone $dtTo;
            // last $interval day(s)
            $dtFrom->modify("-{$interval} day");

            $from = $dtFrom->format('Y-m-d');
            $to = $dtTo->format('Y-m-d');
        }
        return [$from, $to];
    }
}
