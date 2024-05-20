<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Purchase\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Purchase\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * Permission category enum.
 *
 * @package Modules\Purchase\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class PermissionCategory extends Enum
{
    public const INVOICE = 1;

    public const ARTICLE = 2;

    public const ORDER = 3;
}
