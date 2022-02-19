<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Purchase\OrderSuggestion
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Purchase\OrderSuggestion;

use phpOMS\Stdlib\Base\Enum;

/**
 * Suggestion type enum.
 *
 * @package Modules\Purchase\OrderSuggestion
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
abstract class OrderSuggestionType extends Enum
{
    public const AVAILABLE = 1; // Suggestion based on: available stock + current orders

    public const HISTORIC = 2; // Suggestion based on: available stock + current orders + historic demand

    public const TREND = 3; // Suggestion based on: available stock + current orders + trend
}
