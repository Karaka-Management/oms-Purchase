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

use Modules\Purchase\Models\OrderSuggestionMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\DataStorage\Database\Query\OrderType;
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
final class BackendController extends Controller
{
    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPurchaseInvoiceList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Backend/invoice-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1003001001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPurchaseInvoiceCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Backend/invoice-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1003001001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPurchaseArticleList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Backend/article-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1003001001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPurchaseArticleCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Backend/article-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1003001001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPurchaseArticleView(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Backend/article-view');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1003001001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPurchaseOrderSuggestion(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Backend/article-order-suggestion');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002105001, $request, $response);

        $view->data['suggestions'] = OrderSuggestionMapper::get()
            ->with('createdBy')
            ->with('elements')
            ->with('elements/supplier')
            ->with('elements/item')
            ->with('elements/bill')
            ->with('elements/item/l11n')
            ->with('elements/item/l11n/type')
            ->where('id', (int) $request->getData('id'))
            ->where('elements/item/l11n/language', $response->header->l11n->language)
            ->where('elements/item/l11n/type/title', ['name1', 'name2'], 'IN')
            ->sort('elements/supplier', OrderType::ASC)
            ->execute();

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPurchaseOrderSuggestionCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Backend/order-suggestion-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002105001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPurchaseOrderSuggestionList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Purchase/Theme/Backend/order-suggestion-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002105001, $request, $response);

        $view->data['suggestions'] = $this->app->moduleManager->get('Purchase', 'Cli')
            ->calculateSuggestions($response->header->l11n->language);

        return $view;
    }
}
