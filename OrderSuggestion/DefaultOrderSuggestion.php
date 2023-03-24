<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   OrderSuggestion
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Purchase\OrderSuggestion;

use Modules\Purchase\Models\OrderSuggestionInterface;

/**
 * OMS order suggestion class
 *
 * @package Modules\Purchase\OrderSuggestion
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class DefaultOrderSuggestion implements OrderSuggestionInterface
{
    /**
     * Which data should be used to create the order suggestion?
     *
     * @var int
     * @since 1.0.0
     */
    public int $suggestionType = OrderSuggestionType::HISTORIC;

    /**
     * Comparison duration type.
     *
     * @var int
     * @since 1.0.0
     */
    public int $historicComparisonDurationType = OrderSuggestionComparisonDurationType::ANNUALY;

    /**
     * How many years should used for the analysis.
     *
     * @var int
     * @since 1.0.0
     */
    public int $historicComparisonDuration = 1;

    /**
     * Which averaging method should be used?
     *
     * @var int
     * @since 1.0.0
     */
    public int $historicAveragingMethod = OrderSuggestionAveragingMethod::MEAN;

    /**
     * What should the focus of the order suggestion be?
     *
     * @var int
     * @since 1.0.0
     */
    public int $optimizationType = OrderSuggestionOptimizationType::PRICE;

    /**
     * How greedy should the algorithm be?
     *
     * The number represents how many items in % can be bought in additon to the optimal stock quantity.
     *
     * Higher means more room for adjustments.
     *
     * @var int
     * @since 1.0.0
     */
    public int $optimizationGreediness = 10;

    /**
     * For how many days would you like to have stock?
     *
     * Or in other words, higher means you need to order less often per year.
     *
     * Only relevant if OptimizationType::AVAILABILITY
     *
     * @var int
     * @since 1.0.0
     */
    public int $optimizationDays = 0;

    /**
     * Maximum available budget.
     *
     * 0 = infinite budget
     *
     * If a budget is defined and the optimal order is > than the available budget,
     * the algorithm will try to order as many different items as possible.
     *
     * If not all items can be ordered will try to order the items with the highest profit.
     *
     * @var int
     * @since 1.0.0
     */
    public int $maxAvailableBudget = 0;

    /**
     * item data for algorithm:
     *         number
     *         name
     *         purchase price per volume
     *         minimum quantity per volume
     *         delivery time / lead time
     *         in-house processing time (e.g. qs time, labelling, packaging)
     *         ordered (order confirmations)
     *         stock available
     *         offers
     *         avg. profit
     *         demand in period N
     */
}
