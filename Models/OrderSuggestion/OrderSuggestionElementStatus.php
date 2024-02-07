<?php
/**
 * Jingga
 *
 * PHP Version 8.1
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
abstract class OrderSuggestionElementStatus extends Enum
{
    public const MODIFIED = 1;

    public const CALCULATED = 2;
}
