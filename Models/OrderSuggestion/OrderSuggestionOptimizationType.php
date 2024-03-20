<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Purchase\Models\OrderSuggestion
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Purchase\Models\OrderSuggestion;

use phpOMS\Stdlib\Base\Enum;

/**
 * Suggestion type enum.
 *
 * @package Modules\Purchase\Models\OrderSuggestion
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class OrderSuggestionOptimizationType extends Enum
{
    public const ITEM_SPECIFIC = 0; // Suggestion focuses on an availability for X days, days need to be specified in the algorithm.

    public const AVAILABILITY = 1; // Suggestion focuses on an availability for X days, days need to be specified in the algorithm.

    public const COST = 2; // Suggestion focuses on creating better prices if volume discounts exist.

    public const JUST_IN_TIME = 3; // Suggestion focuses on calculating minimum stock quantities.
}
