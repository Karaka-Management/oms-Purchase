<?php
declare(strict_types=1);

use phpOMS\Router\RouteVerb;

return [
    '^/purchase/order/suggestion(\?.*$|$)' => [
        [
            'dest' => '\Modules\Purchase\Controller\CliController:cliGenerateOrderSuggestion',
            'verb' => RouteVerb::ANY,
        ],
    ],
];
