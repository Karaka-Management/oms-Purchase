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
abstract class OrderSuggestionComparisonDurationType extends Enum
{
    public const ANNUALY = 1;

    public const MONTHLY = 2; // The basis is every month, the different months are considered the same

    public const MONTHLY_ANNUAL = 3; // The basis is the same month, e.g. helpful for seasonal data
}
