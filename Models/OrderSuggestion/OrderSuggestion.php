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
use phpOMS\Stdlib\Base\FloatInt;

/**
 * OrderSuggestion class.
 *
 * @package Modules\Purchase\Models\OrderSuggestion
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class OrderSuggestion
{
    public int $id = 0;

    public int $status = OrderSuggestionStatus::DRAFT;

    public Account $createdBy;

    public \DateTimeImmutable $createdAt;

    /**
     * Order elements
     *
     * @var OrderSuggestionElement[]
     * @since 1.0.0
     */
    public array $elements = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
    }

    /**
     * Calculate total costs of order
     *
     * @return FloatInt
     *
     * @since 1.0.0
     */
    public function getTotalCosts() : FloatInt
    {
        $total = new FloatInt();
        foreach ($this->elements as $element) {
            $total->add($element->costs);
        }

        return $total;
    }
}
