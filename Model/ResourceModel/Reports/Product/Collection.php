<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 *
 * See COPYING.txt for license details.
 */
namespace Faonni\ProductMostViewed\Model\ResourceModel\Reports\Product;

use Magento\Reports\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Product Most Viewed Collection
 */
class Collection extends ProductCollection
{
    /**
     * Add Views Count
     *
     * @param string $from
     * @param string $to
     * @return \Faonni\ProductMostViewed\Model\ResourceModel\Reports\Product\Collection
     */
    public function addViewsCount($from = '', $to = '')
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        $eventTypes = $this->_eventTypeFactory->create()->getCollection();
        $productViewEvent = null;
        foreach ($eventTypes as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = (string)$eventType->getId();
                break;
            }
        }

        $this->getSelect()->reset()->from(
            ['report_table_views' => $this->getTable('report_event')],
            ['views' => 'COUNT(report_table_views.event_id)']
        )->join(
            ['e' => $this->getProductEntityTableName()],
            'e.entity_id = report_table_views.object_id'
        )->where(
            'report_table_views.event_type_id = ?',
            $productViewEvent
        )->group(
            'e.entity_id'
        )->order(
            'views ' . self::SORT_ORDER_DESC
        )->having(
            'COUNT(report_table_views.event_id) > ?',
            0
        );

        if ($from != '' && $to != '') {
            $this->getSelect()->where('logged_at >= ?', $from)->where('logged_at <= ?', $to);
        }
        return $this;
    }
}
