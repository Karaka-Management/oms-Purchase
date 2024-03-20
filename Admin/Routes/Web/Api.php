<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Purchase\Controller\ApiController;
use Modules\Purchase\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/purchase/order/suggestion(\?.*|$)' => [
        [
            'dest'       => '\Modules\Purchase\Controller\ApiController:apiOrderSuggestionCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::ORDER,
            ],
        ],
        [
            'dest'       => '\Modules\Purchase\Controller\ApiController:apiOrderSuggestionUpdate',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::ORDER,
            ],
        ],
        [
            'dest'       => '\Modules\Purchase\Controller\ApiController:apiOrderSuggestionDelete',
            'verb'       => RouteVerb::DELETE,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::ORDER,
            ],
        ],
    ],
    '^.*/purchase/order/suggestion/bill(\?.*|$)' => [
        [
            'dest'       => '\Modules\Purchase\Controller\ApiController:apiOrderSuggestionBillCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::ORDER,
            ],
        ],
    ],
];
