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
abstract class OrderSuggestionComparisonDurationType extends Enum
{
    public const ANNUALLY = 1;

    public const MONTHLY = 2; // The basis is every month, the different months are considered the same

    public const WEEKLY = 3;

    public const MONTHLY_ANNUAL = 4; // The basis is the same monthly, e.g. helpful for seasonal data

    public const WEEKLY_ANNUAL = 5; // The basis is the same weekly, e.g. helpful for seasonal data
}
