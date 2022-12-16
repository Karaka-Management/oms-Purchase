<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Purchase\OrderSuggestion
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Purchase\OrderSuggestion;

use phpOMS\Stdlib\Base\Enum;

/**
 * Suggestion type enum.
 *
 * @package Modules\Purchase\OrderSuggestion
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class OrderSuggestionOptimizationType extends Enum
{
    public const PRICE = 1; // Suggestion focuses on creating better prices if volume discounts exist.

    public const JUST_IN_TIME = 2; // Suggestion focuses on calculating minimum stock quantities.

    public const AVAILABILITY = 3; // Suggestion focuses on an availability for X days, days need to be specified in the algorithm.
}
