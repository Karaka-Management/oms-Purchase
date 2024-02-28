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

use Modules\Admin\Models\AccountMapper;
use Modules\Billing\Models\BillMapper;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Client mapper class.
 *
 * @package Modules\Purchase\Models\OrderSuggestion
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Client
 * @extends DataMapperFactory<T>
 */
final class OrderSuggestionElementMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'purchase_order_suggestion_element_id'         => ['name' => 'purchase_order_suggestion_element_id',         'type' => 'int',      'internal' => 'id'],
        'purchase_order_suggestion_element_status'         => ['name' => 'purchase_order_suggestion_element_status',         'type' => 'int',   'internal' => 'status'],
        'purchase_order_suggestion_element_updated_by' => ['name' => 'purchase_order_suggestion_element_updated_by', 'type' => 'int',   'internal' => 'modifiedBy'],
        'purchase_order_suggestion_element_updated_at'     => ['name' => 'purchase_order_suggestion_element_updated_at',     'type' => 'DateTimeImmutable',      'internal' => 'modifiedAt'],
        'purchase_order_suggestion_element_suggestion'       => ['name' => 'purchase_order_suggestion_element_suggestion',       'type' => 'int',      'internal' => 'suggestion'],
        'purchase_order_suggestion_element_item'       => ['name' => 'purchase_order_suggestion_element_item',       'type' => 'int',      'internal' => 'item'],
        'purchase_order_suggestion_element_bill'       => ['name' => 'purchase_order_suggestion_element_bill',       'type' => 'int',      'internal' => 'bill'],
        'purchase_order_suggestion_element_supplier'       => ['name' => 'purchase_order_suggestion_element_supplier',       'type' => 'int',      'internal' => 'supplier'],
        'purchase_order_suggestion_element_quantity'       => ['name' => 'purchase_order_suggestion_element_quantity',       'type' => 'Serializable',      'internal' => 'quantity'],
        'purchase_order_suggestion_element_costs'       => ['name' => 'purchase_order_suggestion_element_costs',       'type' => 'Serializable',      'internal' => 'costs'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'purchase_order_suggestion_element';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'purchase_order_suggestion_element_id';

    /**
     * Created at column
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'purchase_order_suggestion_element_updated_at';

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'modifiedBy' => [
            'mapper'   => AccountMapper::class,
            'external' => 'purchase_order_suggestion_element_modified_by',
        ],
        'supplier' => [
            'mapper'   => SupplierMapper::class,
            'external' => 'purchase_order_suggestion_element_supplier',
        ],
        'item' => [
            'mapper'   => ItemMapper::class,
            'external' => 'purchase_order_suggestion_element_item',
        ],
        'bill' => [
            'mapper'   => BillMapper::class,
            'external' => 'purchase_order_suggestion_element_bill',
        ],
    ];
}
