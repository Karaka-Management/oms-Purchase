<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Purchase
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionOptimizationType;
use phpOMS\Uri\UriFactory;

$optimizationAlgorithms = OrderSuggestionOptimizationType::getConstants();

// @feature Allow to specify a supplier

// @feature Allow to specify a item segmentation. Ideally segment->section->sales_group->product_group

/**
 * @var \phpOMS\Views\View $this
 */
echo $this->data['nav']->render();
?>
<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="portlet">
            <form id="orderSuggestionCreate" action="<?= UriFactory::build('{/api}purchase/order/suggestion'); ?>" method="put">
            <div class="portlet-body">
                <!--
                <div class="form-group">
                    <label for="iOptimizationAlgorithm"><?= $this->getHtml('Algorithm'); ?></label>
                    <select id="iOptimizationAlgorithm" name="optimization">
                        <?php foreach ($optimizationAlgorithms as $alg) : ?>
                        <option value="<?= $alg; ?>"><?= $this->getHtml(':OptimizationAlgorithm-' . $alg); ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="portlet-separator"></div>
            <div class="portlet-body">
                <div class="form-group">
                    <label for="iSupplier"><?= $this->getHtml('Supplier'); ?></label>
                    <input id="iSupplier" name="supplier" type="text">
                </div>
            </div>
            <div class="portlet-separator"></div>
            <div class="portlet-body">
                <div class="form-group">
                    <label for="iItemSegment"><?= $this->getHtml('Segment'); ?></label>
                    <input id="iItemSegment" name="item_segment" type="text">
                </div>

                <div class="form-group">
                    <label for="iItemSection"><?= $this->getHtml('Section'); ?></label>
                    <input id="iItemSection" name="item_section" type="text">
                </div>

                <div class="form-group">
                    <label for="iItemSalesGroup"><?= $this->getHtml('SalesGroup'); ?></label>
                    <input id="iItemSalesGroup" name="item_salesgroup" type="text">
                </div>

                <div class="form-group">
                    <label for="iItemProductGroup"><?= $this->getHtml('ProductGroup'); ?></label>
                    <input id="iItemProductGroup" name="item_productgroup" type="text">
                </div>
            </div>
            <div class="portlet-separator"></div>
            <div class="portlet-body">
                <div class="form-group">
                    <label for="iMinRange"><?= $this->getHtml('MinRange'); ?></label>
                    <input id="iMinRange" name="minrange" type="text">
                </div>
            -->
                <div class="form-group">
                    <label class="checkbox" for="iIrrelevantItems">
                        <input id="iIrrelevantItems" name="hide_irrelevant" type="checkbox" value="1" checked>
                        <span class="checkmark"></span>
                        <?= $this->getHtml('HideIrrelevant'); ?>
                    </label>
                </div>
            </div>
            <div class="portlet-foot">
                <input type="submit" value="<?= $this->getHtml('Analyze'); ?>">
            </div>
            </form>
        </div>
    </div>
</div>
