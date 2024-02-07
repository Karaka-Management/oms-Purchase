<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\HumanResourceManagement
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\HumanResourceManagement\Controller;

use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\OperatingSystem;
use phpOMS\System\SystemType;
use phpOMS\System\SystemUtils;

/**
 * HumanResourceManagement controller class.
 *
 * @package Modules\HumanResourceManagement
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Api method to create an employee from an existing account
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiOrderSuggestionSimulate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        // Offload bill parsing to cli
        $cliPath = \realpath(__DIR__ . '/../../../cli.php');
        if ($cliPath === false) {
            return;
        }

        $supplier = $request->getDataString('supplier');
        $productGroup = $request->getDataInt('product_group');
        $showIrrelevant = !($request->getDataBool('hide_irrelevant') ?? true);

        try {
            SystemUtils::runProc(
                OperatingSystem::getSystem() === SystemType::WIN ? 'php.exe' : 'php',
                \escapeshellarg($cliPath)
                    . ' /purchase/order/suggestion/create'
                    . ($supplier === null ? '' : ' -supplier ' . \escapeshellarg($supplier))
                    . ($productGroup === null ? '' : ' -pgroup ' . \escapeshellarg((string) $productGroup))
                    . ($showIrrelevant === null ? '' : ' -irrelevant ' . \escapeshellarg((string) $showIrrelevant))
                    . ' -user ' . ((int) $request->header->account),
                $request->getDataBool('async') ?? true
            );
        } catch (\Throwable $t) {
            $response->header->status = RequestStatusCode::R_400;
            $this->app->logger->error($t->getMessage());
        }
    }
}
