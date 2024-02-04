<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Purchase
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Purchase\Controller;

use Modules\Billing\Models\SalesBillMapper;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\ItemManagement\Models\StockIdentifierType;
use Modules\Organization\Models\Attribute\UnitAttributeMapper;
use Modules\SupplierManagement\Models\NullSupplier;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Stdlib\Base\SmartDateTime;
use phpOMS\Views\View;

/**
 * Purchase controller class.
 *
 * @package Modules\Purchase
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class CliController extends Controller
{
    /**
     * Method which generates the general settings view.
     *
     * In this view general settings for the entire application can be seen and adjusted. Settings which can be modified
     * here are localization, password, database, etc.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Response can be rendered
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function cliGenerateOrderSuggestion(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Cli/order-suggestion');

        // @feature Implement a version which doesn't rely on the warehouse management but estimates stocks based
        //      on historic sales
        if (!$this->app->moduleManager->isActive('WarehouseManagement')
            || !$this->app->moduleManager->isActive('Billing')
        ) {
            return $view;
        }

        // @todo define order details per item+stock

        // @question Consider to adjust range algorithm from months to weeks

        // @question Consider to save suggestion as model in db
        //      This would allow users to work on it for a longer time
        //      It would also allow for an easier approval process
        $items = ItemMapper::getAll()
            ->with('attributes')
            ->with('attributes/type')
            ->where('stockIdentifier', StockIdentifierType::NONE, '!=')
            ->where('attributes/type/name', [
                'primary_supplier', 'lead_time', 'qc_time',
                'maximum_stock_quantity', 'minimum_stock_quantity',
                'minimum_order_quantity',
                'minimum_stock_range', 'order_quantity_steps',
                'order_suggestion_type', 'order_suggestion_optimization_type',
                'order_suggestion_history_duration', 'order_suggestion_averaging_method',
                'order_suggestion_comparison_duration_type',
            ], 'IN')
            ->execute();

        $itemIds = \array_map(function (Item $item) {
            return $item->id;
        }, $items);

        $start = SmartDateTime::startOfMonth();
        $start->smartModify(0, -12);

        $end = SmartDateTime::endOfMonth();
        $end->smartModify(0, -1);

        $salesHistory = SalesBillMapper::getItemMonthlySalesQuantity($itemIds, $start, $end);
        $distributions = \Modules\WarehouseManagement\Models\StockMapper::getStockDistribution($itemIds);

        $unitAttribute = UnitAttributeMapper::get()
            ->with('type')
            ->with('value')
            ->where('ref', $this->app->unitId)
            ->where('type/name', 'business_year_start')
            ->execute();

        $businessStart = $unitAttribute->id === 0 ? 1 : $unitAttribute->value->getValue();

        $historyStart = (int) $start->format('m');
        $historyEnd = (int) $end->format('m');

        $suggestions = [];
        foreach ($items as $item) {
            $maxHistoryDuration = $item->getAttribute('order_suggestion_history_duration')->value->getValue() ?? 12;

            $salesForecast = [];

            // If item is new, the history start is shifted
            $tempHistoryStart = ((int) $item->createdAt->format('Y')) >= ((int) $start->format('Y'))
                && ((int) $item->createdAt->format('m')) >= ((int) $start->format('m'))
                ? (int) $item->createdAt('m') // @todo Bad if created at end of month (+1 also not possible because of year change)
                : $historyStart;

            $actualHistoricDuration = \min(
                $maxHistoryDuration,
                SmartDateTime::calculateMonthIndex($historyEnd, $tempHistoryStart)
            );

            ///////////////////////////////////////////////////////////////////////////////////////////////////////////
            // get historic sales
            //      use order_suggestion_history_duration
            //      Or 12 month
            //          If less than 12 month use what we have
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////

            foreach ($salesHistory as $month) {
                $currentMonthIndex = SmartDateTime::calculateMonthIndex($month['month'], $tempHistoryStart);

                // @bug Doesn't work if maxHistoryDuration > 12 months
                if ($month['item'] !== $item->id
                    || 12 - SmartDateTime::calculateMonthIndex($month['month'], $tempHistoryStart) >= $maxHistoryDuration
                ) {
                    continue;
                }

                $salesForecast[$currentMonthIndex] = $month['quantity'];
            }

            ///////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Range based calculation
            // calculate current range using historic sales
            //      above calculation provides array as forecast which allows to easily impl. seasonal data
            // get minimum range
            //      Either directly from attribute minimum_stock_range
            //      Or from minimum_stock_quantity
            // calculate quantity needed incl. lead_time and qc_time for minimum range
            // make sure that the quantity is rounded to the next closest quantity step
            // make sure that at least the minimum order quantity is fulfilled
            // make sure that the maximum order quantity is not exceeded
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////

            // Calculate current range using historic sales + other current stats
            $totalHistoricSales = \array_sum($salesForecast);
            $avgMonthlySales = $totalHistoricSales / $actualHistoricDuration;

            $totalStockQuantity = 0;
            foreach ($distributions['dists'][$item->id] ?? [] as $dist) {
                $totalStockQuantity += $dist->quantity;
            }

            $totalReservedQuantity = $distributions['reserved'][$item->id] ?? 0;
            $totalOrderedQuantity = $distributions['ordered'][$item->id] ?? 0;

            $currentRangeStock    = ($totalStockQuantity + $totalOrderedQuantity) / $avgMonthlySales;
            $currentRangeReserved = ($totalStockQuantity + $totalOrderedQuantity - $totalReservedQuantity) / $avgMonthlySales;

            // Get minimum range we want
            $minimumStockRange    = $item->getAttribute('minimum_stock_range')->value->getValue() ?? 0;
            $minimumStockQuantity = $item->getAttribute('minimum_stock_quantity')->value->getValue() ?? 0;
            $minimumStockRange    = \max($minimumStockQuantity / $avgMonthlySales, $minimumStockRange);

            $minimumOrderQuantity = $item->getAttribute('minimum_order_quantity')->value->getValue() ?? 0;
            $orderQuantityStep = $item->getAttribute('order_quantity_steps')->value->getValue() ?? 1;

            $leadTime = $item->getAttribute('lead_time')->value->getValue() ?? 3; // in days

            // @todo Business hours don't have to be 8 hours
            $qcTime = ($item->getAttribute('qc_time')->value->getValue() ?? 0) / (8 * 60); // from minutes -> days

            // Overhead time in days by estimating at least 1 week worth of order quantity
            $estimatedOverheadTime = $leadTime + $qcTime * \max($minimumOrderQuantity, $avgMonthlySales / 4);

            $orderQuantity = 0;
            $rangeDiff = $minimumStockRange - ($currentRangeReserved - $estimatedOverheadTime / 30);
            if ($rangeDiff > 0) {
                $orderQuantity = $rangeDiff * $avgMonthlySales;
                $orderQuantity = \max($minimumOrderQuantity, $orderQuantity);

                if ($orderQuantity !== $minimumOrderQuantity
                    && ($orderQuantity - $minimumOrderQuantity) % $orderQuantityStep !== 0
                ) {
                    $orderQuantity = ($orderQuantity - $minimumOrderQuantity) % $orderQuantityStep;
                }
            }

            $orderRange = $orderQuantity / $avgMonthlySales;

            $supplier = new NullSupplier($item->getAttribute('primary_supplier')->value->getValue());

            $internalRequest = new HttpRequest();
            $internalRequest->setData('price_item', $item->id);

            $price = $this->app->moduleManager->get('Billing', 'ApiPrice')->findBestPrice(
                $internalRequest,
                null,
                null,
                $supplier
            );

            // @question Consider to add gross price
            $suggestions[$item->id] = [
                'supplier' => $supplier->id,
                'quantity' => $orderQuantity,
                'singlePrice' => $price['bestActualPrice'],
                'totalPrice' => (int) ($price['bestActualPrice'] * $orderQuantity / 10000),
                'range_stock' => $currentRangeStock, // range only considering stock + ordered
                'range_reserved' => $currentRangeReserved, // range considering stock - reserved + ordered
                'range_ordered' => $orderRange, // range ADDED with suggested new order quantity
            ];
        }

        return $view;
    }
}
