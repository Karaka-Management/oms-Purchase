<?php
/**
 * Jingga
 *
 * PHP Version 8.1
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
    '^.*/purchase/invoice/create.*$' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseInvoiceCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::INVOICE,
            ],
        ],
    ],
    '^.*/purchase/invoice/list.*$' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseInvoiceList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::INVOICE,
            ],
        ],
    ],
    '^.*/purchase/article/list.*$' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseArticleList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::ARTICLE,
            ],
        ],
    ],
    '^.*/purchase/article/create.*$' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseArticleCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::ARTICLE,
            ],
        ],
    ],
    '^.*/purchase/article/profile.*$' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseArticleProfile',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::ARTICLE,
            ],
        ],
    ],
    '^.*/purchase/order/suggestion.*$' => [
        [
            'dest'       => '\Modules\Purchase\Controller\BackendController:viewPurchaseOrderSuggestion',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::ARTICLE,
            ],
        ],
    ],
];
