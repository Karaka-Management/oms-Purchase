<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   OrderSuggestion
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Purchase\Models\OrderSuggestion;

/**
 * OMS order suggestion class
 *
 * @package Modules\Purchase\Models\OrderSuggestion
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
    public int $historicComparisonDurationType = OrderSuggestionComparisonDurationType::ANNUALLY;

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
    public int $optimizationType = OrderSuggestionOptimizationType::AVAILABILITY;

    /**
     * How greedy should the algorithm be?
     *
     * The number represents how many items in % can be bought in addition to the optimal stock quantity.
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
}
