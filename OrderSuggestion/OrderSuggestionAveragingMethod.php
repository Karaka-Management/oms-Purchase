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
abstract class OrderSuggestionAveragingMethod extends Enum
{
    public const MEAN = 1;

    public const MEDIAN = 2;

    public const MEAN_WEIGHTED = 3;
}
