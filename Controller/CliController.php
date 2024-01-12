<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Purchase
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Purchase\Controller;

use Modules\ItemManagement\Models\ItemMapper;
use Modules\ItemManagement\Models\StockIdentifierType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Purchase controller class.
 *
 * @package Modules\Purchase
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class CliController extends Controller
{
    /**
     * Method which generates the general settings view.
     *
     * In this view general settings for the entire application can be seen and adjusted. Settings which can be modified
     * here are localization, password, database, etc.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Response can be rendered
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function cliGenerateOrderSuggestion(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        // @todo define order details per item+stock
        $items = ItemMapper::getAll()
            ->with('attributes')
            ->with('attributes/type')
            ->where('stockIdentifier', StockIdentifierType::NONE, '!=')
            ->where('attributes/type/name', [
                'primary_supplier', 'lead_time', 'qc_time',
                'maximum_stock_quantity', 'minimum_stock_quantity',
                'maximum_order_interval', 'minimum_order_quantity',
                'order_suggestion_type', 'order_suggestion_optimization_type',
                'order_suggestion_history_duration', 'order_suggestion_averaging_method',
                'order_suggestion_comparison_duration_type'
            ], 'IN')
            ->execute();

        foreach ($items as $item) {

        }
    }

}
