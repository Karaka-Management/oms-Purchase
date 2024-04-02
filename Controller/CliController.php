<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Purchase
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Purchase\Controller;

use Modules\Admin\Models\NullAccount;
use Modules\Billing\Models\Price\PriceType;
use Modules\Billing\Models\SalesBillMapper;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\ItemManagement\Models\ItemStatus;
use Modules\ItemManagement\Models\StockIdentifierType;
use Modules\Organization\Models\Attribute\UnitAttributeMapper;
use Modules\Purchase\Models\OrderSuggestion\OrderSuggestion;
use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionElement;
use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionElementStatus;
use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionMapper;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Math\Functions\Functions;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Stdlib\Base\FloatInt;
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
 *
 * @feature Create feature which re-calculates some of the item number (minimum_stock_range, lead_time, ...) based on history numbers
 *      https://github.com/Karaka-Management/oms-Purchase/issues/3
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

        $orderSuggestion = $this->createSuggestionFromRequest($request);
        $this->createModel($request->header->account, $orderSuggestion, OrderSuggestionMapper::class, 'order_suggestion', $request->getOrigin());

        return $view;
    }

    /**
     * Create a suggestion
     *
     * @param RequestAbstract $request Request
     *
     * @return OrderSuggestion
     *
     * @since 1.0.0
     */
    public function createSuggestionFromRequest(RequestAbstract $request) : OrderSuggestion
    {
        $showIrrelevant = $request->getDataBool('-irrelevant') ?? false;
        $now            = new \DateTime('now');

        $suggestion            = new OrderSuggestion();
        $suggestion->createdBy = new NullAccount($request->getDataInt('-user') ?? 1);

        // @todo define order details per item+stock

        // @question Consider to adjust range algorithm from months to weeks

        // @question Consider to save suggestion as model in db
        //      This would allow users to work on it for a longer time
        //      It would also allow for an easier approval process
        /** @var \Modules\ItemManagement\Models\Item[] $items */
        $items = ItemMapper::getAll()
            ->with('container')
            ->with('l11n')
            ->with('l11n/type')
            ->with('attributes')
            ->with('attributes/type')
            ->where('status', ItemStatus::ACTIVE)
            ->where('stockIdentifier', StockIdentifierType::NONE, '!=')
            ->where('attributes/type/name', [
                'lead_time', 'admin_time',
                'maximum_stock_quantity', 'minimum_stock_quantity',
                'minimum_order_quantity',
                'minimum_stock_range', 'order_quantity_steps',
                'order_suggestion_type', 'order_suggestion_optimization_type',
                'order_suggestion_history_duration', 'order_suggestion_averaging_method',
                'order_suggestion_comparison_duration_type',
                'segment', 'section', 'sales_group', 'product_group', 'product_type',
            ], 'IN')
            ->executeGetArray();

        // @todo check for supplier of item if set
        // @todo check for product type of item if set

        // @todo Implement item dependencies (i.e. for production)
        // @todo Consider production requests
        //      Exclude items only created through production (finished + semi-finished)
        //      Calculate raw material requirement based on
        //          Finished products sales
        //          Current productions = reserved
        //          Not based on sales of the raw material itself = 0 (unless it is also directly sold)

        $itemIds = \array_map(function (Item $item) {
            return $item->id;
        }, $items);

        $start = SmartDateTime::startOfMonth();
        $start->smartModify(0, -12);

        $end = SmartDateTime::endOfMonth();
        $end->smartModify(0, -1);

        $salesHistory  = SalesBillMapper::getItemMonthlySalesQuantity($itemIds, $start, $end);
        $distributions = \Modules\WarehouseManagement\Models\StockMapper::getStockDistribution($itemIds);

        $unitAttribute = UnitAttributeMapper::get()
            ->with('type')
            ->with('value')
            ->where('ref', $this->app->unitId)
            ->where('type/name', 'business_year_start')
            ->execute();

        $businessStart = $unitAttribute->id === 0 ? 1 : $unitAttribute->value->valueInt;

        $historyStart = (int) $start->format('m');
        $historyEnd   = (int) $end->format('m');

        foreach ($items as $item) {
            $maxHistoryDuration = $item->getAttribute('order_suggestion_history_duration')->value->valueInt ?? 12;

            $salesForecast = [];

            // If item is new, the history start is shifted
            $tempHistoryStart = ((int) $item->createdAt->format('Y')) >= ((int) $start->format('Y'))
                && ((int) $item->createdAt->format('m')) >= ((int) $start->format('m'))
                ? (int) $item->createdAt->format('m') // @todo Bad if created at end of month (+1 also not possible because of year change)
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
            // calculate quantity needed incl. lead_time and admin_time for minimum range
            // make sure that the quantity is rounded to the next closest quantity step
            // make sure that at least the minimum order quantity is fulfilled
            // make sure that the maximum order quantity is not exceeded
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////

            // Calculate current range using historic sales + other current stats
            $totalHistoricSales = \array_sum($salesForecast);
            $avgMonthlySales    = (int) \round($totalHistoricSales / $actualHistoricDuration);

            $totalStockQuantity = 0;
            foreach ($distributions['dists'][$item->id] ?? [] as $dist) {
                $totalStockQuantity += $dist->quantity;
            }

            $totalReservedQuantity = $distributions['reserved'][$item->id] ?? 0;
            $totalOrderedQuantity  = $distributions['ordered'][$item->id] ?? 0;

            $currentRangeStock    = $avgMonthlySales == 0 ? \PHP_INT_MAX : ($totalStockQuantity + $totalOrderedQuantity) / $avgMonthlySales;
            $currentRangeReserved = $avgMonthlySales == 0 ? \PHP_INT_MAX : ($totalStockQuantity + $totalOrderedQuantity - $totalReservedQuantity) / $avgMonthlySales;

            // @todo Sometimes the reserved range is misleading since the company may not be able to deliver that fast anyway
            //      -> the reserved quantity is always a constant (even if we have stock, we wouldn't ship)
            //      -> see SD HTS (depending on other shipments -> not delivered even if available)
            //      -> maybe it's possible to consider the expected delivery time?

            // Get minimum range we want
            $wantedStockRange = $item->getAttribute('minimum_stock_range')->value->valueInt ?? 1;

            $minimumStockQuantity = $item->getAttribute('minimum_stock_quantity')->value->valueInt ?? 0;
            $minimumStockQuantity = (int) \round($minimumStockQuantity * FloatInt::DIVISOR);
            $minimumStockRange    = $avgMonthlySales === 0 ? 0 : $minimumStockQuantity / $avgMonthlySales;
            $minimumStockQuantity = (int) \round($minimumStockRange * $avgMonthlySales);

            $minimumOrderQuantity = $item->getAttribute('minimum_order_quantity')->value->valueInt ?? 0;
            $minimumOrderQuantity = (int) \round($minimumOrderQuantity * FloatInt::DIVISOR);

            $orderQuantityStep = $item->getAttribute('order_quantity_steps')->value->valueInt ?? 1;
            $orderQuantityStep = (int) \round($orderQuantityStep * FloatInt::DIVISOR);

            $leadTime = $item->getAttribute('lead_time')->value->valueInt ?? 3; // in days

            // @todo Business hours don't have to be 8 hours
            // we assume 10 seconds per item if nothing is defined for (invoice handling, stock handling)
            $adminTime = ($item->getAttribute('admin_time')->value->valueInt ?? 10) / (8 * 60 * 60); // from seconds -> days

            // Overhead time in days by estimating at least 1 week worth of order quantity
            $estimatedOverheadTime = $leadTime + $adminTime * \max($minimumOrderQuantity, $avgMonthlySales / 4) / FloatInt::DIVISOR;

            $orderQuantity = 0;
            $orderRange    = 0;

            if ($minimumStockRange - ($currentRangeReserved - $estimatedOverheadTime / 30) > 0) {
                // Iteratively approaching overhead time
                for ($i = 0; $i < 3; ++$i) {
                    $orderRange = $minimumStockRange + $wantedStockRange - ($currentRangeReserved - $estimatedOverheadTime / 30);

                    // @todo Instead of using $orderRange * $avgMonthlySales use the actual forecast sales from above.
                    //      Of course based on the $orderRange = array sum for the orderRange
                    //      Starting at what index? Now? or do we need to shift by reserved?
                    $orderQuantity = $orderRange * $avgMonthlySales;
                    $orderQuantity = \max($minimumOrderQuantity, $orderQuantity);

                    if ($orderQuantity !== $minimumOrderQuantity
                        && \abs($mod = Functions::modFloat($orderQuantity - $minimumOrderQuantity, $orderQuantityStep)) >= 0.01
                    ) {
                        // The if in the brackets rounds the orderQuantity up or down based on closest proximity
                        $orderQuantity = $orderQuantity - $mod + ($orderQuantityStep - $mod < $mod ? $orderQuantityStep : 0) + $minimumOrderQuantity;
                    }

                    $estimatedOverheadTime = $leadTime + $adminTime * $orderQuantity / FloatInt::DIVISOR;
                }
            }

            $orderQuantity = (int) \round($orderQuantity);

            $isNew = $now->getTimestamp() - $item->createdAt->getTimestamp() < 60 * 60 * 24 * 60;

            if (!$showIrrelevant
                && $orderQuantity === 0
                && !$isNew
                && ($currentRangeReserved > 1.0 || $avgMonthlySales === 0)
                && $minimumOrderQuantity * 1.2 <= $currentRangeReserved * $avgMonthlySales
            ) {
                continue;
            }

            $internalRequest = new HttpRequest();
            $internalRequest->setData('price_quantity', $orderQuantity);
            $internalRequest->setData('price_type', PriceType::PURCHASE);

            $price = $this->app->moduleManager->get('Billing', 'ApiPrice')->findBestPrice($internalRequest, $item);

            $supplier = SupplierMapper::get()
                ->with('account')
                ->where('id', (int) $price['supplier'])
                ->execute();

            $element                  = new OrderSuggestionElement();
            $element->status          = OrderSuggestionElementStatus::CALCULATED;
            $element->modifiedBy      = $suggestion->createdBy;
            $element->quantity->value = $orderQuantity;
            $element->item            = $item;
            $element->supplier        = $supplier;
            $element->costs           = new FloatInt((int) ($price['bestActualPrice']->value * $orderQuantity / FloatInt::DIVISOR));

            $suggestion->elements[] = $element;
        }

        return $suggestion;
    }
}
