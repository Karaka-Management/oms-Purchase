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

use Modules\Purchase\Controller\BackendController;
use Modules\Purchase\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^/purchase/order/suggestion/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseOrderSuggestion',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::ORDER,
            ],
        ],
    ],
    '^/purchase/order/suggestion/create(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseOrderSuggestionCreate',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::ORDER,
            ],
        ],
    ],
    '^/purchase/order/suggestion/list(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseOrderSuggestionList',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::ORDER,
            ],
        ],
    ],
];
