<?php

namespace LauserMedia\OrderOverviewArticles\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
  protected $helper;

  public function __construct(
        EntityFactory $entityFactory,
    Logger $logger,
    FetchStrategy $fetchStrategy,
    EventManager $eventManager,
    $mainTable = 'sales_order_grid',
    $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
  )
  {
    parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
  }

    protected function _renderFiltersBefore()
    {
        $orderTable = $this->getTable('sales_order');
        $itemTable = $this->getTable('sales_order_item'); // Korrekte Tabelle für die Bestellartikel

        // Join, um zusätzliche Felder aus der sales_order Tabelle zu holen
        $this->getSelect()->joinLeft(
            ['order' => $orderTable],
            'main_table.entity_id = order.entity_id',
            ['tax_amount', 'discount_amount', 'coupon_code']
        );

        $this->getSelect()->joinLeft(
            ['order_item' => $itemTable],
            'main_table.entity_id = order_item.order_id',
            ['ordered_items' => new \Zend_Db_Expr("GROUP_CONCAT(CONCAT(order_item.name, ' (', order_item.sku, ')') SEPARATOR '; ')")]
        )->group('main_table.entity_id');

        parent::_renderFiltersBefore();
    }
}
