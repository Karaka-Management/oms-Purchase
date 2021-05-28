<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Purchase\Models\OrderSuggestion
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Purchase\OrderSuggestion;

use phpOMS\Stdlib\Base\Enum;

/**
 * Suggestion type enum.
 *
 * @package Modules\Purchase\Models\OrderSuggestion
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
abstract class OrderSuggestionOptimizationType extends Enum
{
    public const PRICE = 1; // Suggestion focuses on creating better prices if volume discounts exist.

    public const JUST_IN_TIME = 2; // Suggestion focuses on calculating minimum stock quantities.

    public const AVAILABILITY = 3; // Suggestion focuses on an availability for X days, days need to be specified in the algorithm.
}
