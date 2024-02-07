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

    public array $elements = [];

    public function __construct()
    {
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
    }
}
