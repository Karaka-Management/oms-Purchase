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

use Modules\Admin\Models\AccountMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * OrderSuggestion mapper class.
 *
 * @package Modules\Purchase\Models\OrderSuggestion
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of OrderSuggestion
 * @extends DataMapperFactory<T>
 */
final class OrderSuggestionMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'purchase_order_suggestion_id'         => ['name' => 'purchase_order_suggestion_id',         'type' => 'int',      'internal' => 'id'],
        'purchase_order_suggestion_status'     => ['name' => 'purchase_order_suggestion_status',         'type' => 'int',   'internal' => 'status'],
        'purchase_order_suggestion_created_by' => ['name' => 'purchase_order_suggestion_created_by', 'type' => 'int',   'internal' => 'createdBy'],
        'purchase_order_suggestion_created_at' => ['name' => 'purchase_order_suggestion_created_at',     'type' => 'DateTimeImmutable',      'internal' => 'createdAt'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'purchase_order_suggestion';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'purchase_order_suggestion_id';

    /**
     * Created at column
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'purchase_order_suggestion_created_at';

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'createdBy' => [
            'mapper'   => AccountMapper::class,
            'external' => 'purchase_order_suggestion_created_by',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'elements' => [
            'mapper'   => OrderSuggestionElementMapper::class,              /* mapper of the related object */
            'table'    => 'purchase_order_suggestion_element',       /* table of the related object, null if no relation table is used (many->1) */
            'external' => null,
            'self'     => 'purchase_order_suggestion_element_suggestion',
        ],
    ];
}
