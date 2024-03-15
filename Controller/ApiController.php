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

use Modules\Billing\Models\Price\PriceType;
use Modules\Billing\Models\SalesBillMapper;
use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionElement;
use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionElementMapper;
use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionMapper;
use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionStatus;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Stdlib\Base\FloatInt;
use phpOMS\Stdlib\Base\SmartDateTime;
use phpOMS\System\OperatingSystem;
use phpOMS\System\SystemType;
use phpOMS\System\SystemUtils;

/**
 * Purchase controller class.
 *
 * @package Modules\Purchase
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Api method to create an employee from an existing account
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiOrderSuggestionCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        // Offload bill parsing to cli
        $cliPath = \realpath(__DIR__ . '/../../../cli.php');
        if ($cliPath === false) {
            return;
        }

        $supplier       = $request->getDataString('supplier');
        $productGroup   = $request->getDataInt('product_group');
        $showIrrelevant = !($request->getDataBool('hide_irrelevant') ?? true);

        try {
            SystemUtils::runProc(
                OperatingSystem::getSystem() === SystemType::WIN ? 'php.exe' : 'php',
                    \escapeshellarg($cliPath)
                    . ' /purchase/order/suggestion/create'
                    . ($supplier === null ? '' : ' -supplier ' . \escapeshellarg($supplier))
                    . ($productGroup === null ? '' : ' -pgroup ' . \escapeshellarg((string) $productGroup))
                    . ($showIrrelevant === null ? '' : ' -irrelevant ' . \escapeshellarg((string) $showIrrelevant))
                    . ' -user ' . ((int) $request->header->account),
                true
            );
        } catch (\Throwable $t) {
            $response->header->status = RequestStatusCode::R_400;
            $this->app->logger->error($t->getMessage());
        }

        $this->createStandardBackgroundResponse($request, $response, []);
    }

    /**
     * Returns data from an order suggestion element.
     *
     * This also re-calculates a lot of values because some depend on the current stock amounts, prices etc.
     *
     * @param OrderSuggestionElement[] $elements Elements of our order
     *
     * @return array<int, array{singlePrice:FloatInt, totalPrice:FloatInt, stock:FloatInt, reserved:FloatInt, ordered:FloatInt, minquantity:FloatInt, minstock:FloatInt, quantitystep:FloatInt, avgsales:FloatInt, range_stock:float, range_reserved:float, range_ordered:float}>
     *
     * @since 1.0.0
     */
    public function getOrderSuggestionElementData(array $elements) : array
    {
        if (empty($elements)) {
            return [];
        }

        $data = [];

        $itemIds = \array_map(function(OrderSuggestionElement $element) {
            return $element->item->id;
        }, $elements);

        $start = SmartDateTime::startOfMonth();
        $start->smartModify(0, -12);

        $end = SmartDateTime::endOfMonth();
        $end->smartModify(0, -1);

        $salesHistory  = SalesBillMapper::getItemMonthlySalesQuantity($itemIds, $start, $end);
        $distributions = \Modules\WarehouseManagement\Models\StockMapper::getStockDistribution($itemIds);

        $historyStart = (int) $start->format('m');
        $historyEnd   = (int) $end->format('m');

        // @todo A lot of the code below is mirrored in the CliController for ALL items.
        //      Pull out some of the code so we only need to maintain one version
        foreach ($elements as $element) {
            $maxHistoryDuration = $element->item->getAttribute('order_suggestion_history_duration')->value->valueInt ?? 12;

            $salesForecast = [];

            // If item is new, the history start is shifted
            $tempHistoryStart = ((int) $element->item->createdAt->format('Y')) >= ((int) $start->format('Y'))
                && ((int) $element->item->createdAt->format('m')) >= ((int) $start->format('m'))
                ? (int) $element->item->createdAt->format('m') // @todo Bad if created at end of month (+1 also not possible because of year change)
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
                if ($month['item'] !== $element->item->id
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
            foreach ($distributions['dists'][$element->item->id] ?? [] as $dist) {
                $totalStockQuantity += $dist->quantity;
            }

            $totalReservedQuantity = $distributions['reserved'][$element->item->id] ?? 0;
            $totalOrderedQuantity  = $distributions['ordered'][$element->item->id] ?? 0;

            $currentRangeStock    = $avgMonthlySales == 0 ? \PHP_INT_MAX : ($totalStockQuantity + $totalOrderedQuantity) / $avgMonthlySales;
            $currentRangeReserved = $avgMonthlySales == 0 ? \PHP_INT_MAX : ($totalStockQuantity + $totalOrderedQuantity - $totalReservedQuantity) / $avgMonthlySales;

            // @todo Sometimes the reserved range is misleading since the company may not be able to deliver that fast anyway
            //      -> the reserved quantity is always a constant (even if we have stock, we wouldn't ship)
            //      -> see SD HTS (depending on other shipments -> not delivered even if available)
            //      -> maybe it's possible to consider the expected delivery time?

            $minimumStockQuantity = $element->item->getAttribute('minimum_stock_quantity')->value->valueInt ?? 0;
            $minimumStockQuantity = (int) \round($minimumStockQuantity * FloatInt::DIVISOR); // @bug why? shouldn't the value already be 10,000?
            $minimumStockRange    = $avgMonthlySales === 0 ? 0 : $minimumStockQuantity / $avgMonthlySales;
            $minimumStockQuantity = (int) \round($minimumStockRange * $avgMonthlySales);

            $minimumOrderQuantity = $element->item->getAttribute('minimum_order_quantity')->value->valueInt ?? 0;
            $minimumOrderQuantity = (int) \round($minimumOrderQuantity * FloatInt::DIVISOR);

            $orderQuantityStep = $element->item->getAttribute('order_quantity_steps')->value->valueInt ?? 1;
            $orderQuantityStep = (int) \round($orderQuantityStep * FloatInt::DIVISOR);

            $orderQuantity = $element->quantity->value;
            $orderRange    = $avgMonthlySales === 0 ? \PHP_INT_MAX : $element->quantity->value / $avgMonthlySales;

            $internalRequest = new HttpRequest();
            $internalRequest->setData('price_quantity', $orderQuantity);
            $internalRequest->setData('price_type', PriceType::PURCHASE);

            $price = $this->app->moduleManager->get('Billing', 'ApiPrice')->findBestPrice($internalRequest, $element->item);

            // @question Consider to add gross price
            $data[$element->item->id] = [
                'singlePrice'    => $price['bestActualPrice'],
                'totalPrice'     => new FloatInt((int) ($price['bestActualPrice']->value * $orderQuantity / FloatInt::DIVISOR)),
                'stock'          => new FloatInt($totalStockQuantity),
                'reserved'       => new FloatInt($totalReservedQuantity),
                'ordered'        => new FloatInt($totalOrderedQuantity),
                'minquantity'    => new FloatInt($minimumOrderQuantity),
                'minstock'       => new FloatInt($minimumStockQuantity),
                'quantitystep'   => new FloatInt($orderQuantityStep),
                'avgsales'       => new FloatInt($avgMonthlySales),
                'range_stock'    => $currentRangeStock, // range only considering stock + ordered
                'range_reserved' => $currentRangeReserved, // range considering stock - reserved + ordered
                'range_ordered'  => $orderRange, // range ADDED with suggested new order quantity
            ];
        }

        return $data;
    }

    /**
     * Api method to delete a suggestion from an existing account
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiOrderSuggestionDelete(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $old = OrderSuggestionMapper::get()
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $new = clone $old;

        $new->status = OrderSuggestionStatus::DELETED;

        $this->updateModel($request->header->account, $old, $new, OrderSuggestionMapper::class, 'order_suggestion', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $new);
    }

    /**
     * Api method to update a suggestion from an existing account
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiOrderSuggestionUpdate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $old = OrderSuggestionMapper::get()
            ->with('elements')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        // Only drafts can get updated
        if ($old->status !== OrderSuggestionStatus::DRAFT) {
            $response->header->status = RequestStatusCode::R_423;
            $this->createInvalidUpdateResponse($request, $response, []);

            return;
        }

        $elements   = $request->getDataJson('element');
        $quantities = $request->getDataJson('quantity');

        // Missmatch -> data corrupted
        if (\count($elements) !== \count($quantities)) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, []);

            return;
        }

        foreach ($elements as $idx => $e) {
            $e    = (int) $e;
            $temp = new FloatInt($quantities[$idx]);

            foreach ($old->elements as $element) {
                if ($element->id !== $e) {
                    continue;
                }

                if ($element->quantity->value === $temp->value) {
                    break;
                }

                $new = clone $element;

                $new->quantity = $temp;

                $internalRequest = new HttpRequest();
                $internalRequest->setData('price_quantity', $new->quantity->value);
                $internalRequest->setData('price_type', PriceType::PURCHASE);

                $price      = $this->app->moduleManager->get('Billing', 'ApiPrice')->findBestPrice($internalRequest, $element->item);
                $new->costs = new FloatInt((int) ($price['bestActualPrice']->value * $new->quantity->value / FloatInt::DIVISOR));

                $this->updateModel($request->header->account, $element, $new, OrderSuggestionElementMapper::class, 'order_suggestion_element', $request->getOrigin());
            }
        }

        $this->createStandardUpdateResponse($request, $response, $old);
    }
}
