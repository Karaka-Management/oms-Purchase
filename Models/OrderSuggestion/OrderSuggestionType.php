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
abstract class OrderSuggestionType extends Enum
{
    public const AVAILABLE = 1; // Suggestion based on: available stock + current orders

    public const HISTORIC = 2; // Suggestion based on: available stock + current orders + historic demand

    public const TREND = 4; // Suggestion based on: available stock + current orders + trend
}
