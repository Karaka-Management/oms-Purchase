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

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;
use Modules\Billing\Models\Bill;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\NullItem;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\Supplier;
use phpOMS\Stdlib\Base\FloatInt;

/**
 * OrderSuggestion class.
 *
 * @package Modules\Purchase\Models\OrderSuggestion
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class OrderSuggestionElement
{
    public int $id = 0;

    public int $status = OrderSuggestionElementStatus::CALCULATED;

    public Account $modifiedBy;

    public \DateTimeImmutable $modifiedAt;

    public int $suggestion = 0;

    public Item $item;

    public ?Bill $bill = null;

    public Supplier $supplier;

    public FloatInt $quantity;

    public FloatInt $costs;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->modifiedBy = new NullAccount();
        $this->modifiedAt = new \DateTimeImmutable('now');
        $this->item       = new NullItem();
        $this->supplier   = new NullSupplier();
        $this->quantity   = new FloatInt();
        $this->costs      = new FloatInt();
    }
}
