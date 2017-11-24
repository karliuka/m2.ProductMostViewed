<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\ProductMostViewed\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Faonni\ProductMostViewed\Model\ResourceModel\Reports\Product\CollectionFactory;

/**
 * Catalog product most viewed items block
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ProductList extends AbstractProduct implements IdentityInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    	
    /**
     * Product collection
     * 
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_itemCollection;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Module manager
     * 
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    
    /**
     * Reports product collection factory
     * 
     * @var \Faonni\ProductMostViewed\Model\ResourceModel\Reports\Product\CollectionFactory
     */
    protected $_productsFactory;    

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Registry $registry
     * @param \Faonni\ProductMostViewed\Model\ResourceModel\Reports\Product\CollectionFactory $productsFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Visibility $catalogProductVisibility,
        Manager $moduleManager,
        Registry $registry,
        CollectionFactory $productsFactory,
        array $data = []
    ) {
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->moduleManager = $moduleManager;
        $this->_coreRegistry = $registry;        
        $this->_productsFactory = $productsFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Prepare items data
     * 
     * @return \Faonni\ProductMostViewed\Block\ProductList
     */
    protected function _prepareData()
    {
        list($from, $to) = $this->getFromTo();
		$this->_itemCollection = $this->_productsFactory->create()
			->addAttributeToSelect('*')
			->addViewsCount($from, $to)
			->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());       
		$numProducts = $this->getNumProducts() ? $this->getNumProducts() : 6;
		
		if ($this->getCurrentCategory()) {
			$this->_itemCollection->addCategoryFilter($this->getCurrentCategory());
		}		
		
		$this->_itemCollection->setPage(1, $numProducts);
        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }
    
    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
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
     * Before rendering html process
     * Prepare items collection
     *
     * @return \Faonni\ProductMostViewed\Block\ProductList
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve items collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getItems()
    {
        return $this->_itemCollection;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }
	
    /**
     * Return from to interval
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
