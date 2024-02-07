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

use phpOMS\Stdlib\Base\FloatInt;
use phpOMS\Stdlib\Base\SmartDateTime;

/**
 * @var \phpOMS\Views\View $this
 */
echo $this->data['nav']->render();
?>
<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
        <div class="portlet">
            <div class="portlet-body">
                <div class="form-group">
                    <label>Supplier</label>
                    <input type="text">
                </div>

                <div class="form-group">
                    <label>Product Group</label>
                    <input type="text">
                </div>
            </div>
        </div>
    </div>



    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
        <div class="portlet">
            <div class="portlet-body">
                <div class="form-group">
                    <label>Algorithm</label>
                    <select>
                        <option>Availability Optimization
                        <option>Cost Optimization
                    </select>
                </div>

                <div class="form-group">
                    <label>Min. range</label>
                    <input type="text">
                </div>
            </div>
        </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
        <div class="portlet">
            <div class="portlet-body">
                <div class="form-group">
                    <label class="checkbox" for="iIrrelevantItems">
                        <input id="iIrrelevantItems" name="hide_irrelevant" type="checkbox" value="1" checked>
                        <span class="checkmark"></span>
                        Hide irrelevant
                    </label>
                </div>
            </div>
            <div class="portlet-foot">
                <!-- @todo Adjust button visibility -->
                <!-- Save if not created ?> -->
                <!-- Order if saved ?> -->
                <!-- None if already order created -->
                <input type="submit" value="Save">
                <input type="submit" value="Order">
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head">Suggestions<i class="g-icon download btn end-xs">download</i></div>
            <div class="slider">
            <table id="billList" class="default sticky">
                <thead>
                <tr>
                    <td>Item
                    <td class="wf-100">
                    <td>Supplier
                    <td>Stock
                    <td>Reserved
                    <td>Ordered
                    <td>Ø Sales
                    <td>Range 1
                    <td>Range 2
                    <td>Min. stock
                    <td>Min. order
                    <td>Steps
                    <td>Ordering
                    <td>Adding
                    <td>New range
                    <td>Price
                    <td>Costs
                <tbody>
                <?php
                    $now = new SmartDateTime('now');
                    $total = new FloatInt();
                    $subtotal = new FloatInt();
                    $lastSupplier = 0;

                    $supplier = $this->request->getDataString('supplier');
                    $hasSupplierSwitch = false;

                    $isFirst = true;

                foreach ($this->data['suggestions'] as $item => $suggestion) :
                    $isNew = $now->getTimestamp() - $suggestion['item']->createdAt->getTimestamp() < 60 * 60 * 24 * 60;

                    // Skip irrelevant items
                    //      No purchase suggestion
                    //      Not new (new = item created in the last 60 days)
                    //      At least 1 month in stock
                    //      At least 20% above min. stock
                    if ($suggestion['quantity']->value === 0
                        && !$isNew
                        && ($suggestion['range_reserved'] > 1.0 || $suggestion['avgsales']->value === 0)
                        && $suggestion['minquantity']->value * 1.2 <= $suggestion['range_reserved'] * $suggestion['avgsales']->value
                    ) {
                        continue;
                    }

                    $total->add($suggestion['totalPrice']);
                    $subtotal->add($suggestion['totalPrice']);
                    $container = \reset($suggestion['item']->container);

                    $class = '';
                    if ($suggestion['quantity']->value !== 0) {
                        $class = ' class="highlight-2"';
                    }
                ?>
                <?php
                    if (empty($supplier) && $lastSupplier !== $suggestion['supplier']->id && !$isFirst) :
                        $hasSupplierSwitch = true;
                        $lastSupplier = $suggestion['supplier']->id;
                ?>
                    <tr class="highlight-7">
                        <td colspan="16"><?= $this->printHtml($suggestion['supplier']->account->name1); ?> <?= $this->printHtml($suggestion['supplier']->account->name2); ?>
                        <td><?= $total->getAmount(); ?>
                <?php
                    $subtotal = new FloatInt();
                    endif;

                    $isFirst = false;
                ?>
                <tr>
                    <td><?= $this->printHtml($suggestion['item']->number); ?>
                    <td><?= $this->printHtml($suggestion['item']->getL11n('name1')->content); ?> <?= $this->printHtml($suggestion['item']->getL11n('name1')->content); ?>
                    <td><?= $this->printHtml($suggestion['supplier']->number); ?>
                    <td><?= $suggestion['stock']->getAmount($container->quantityDecimals); ?>
                    <td><?= $suggestion['reserved']->getAmount($container->quantityDecimals); ?>
                    <td><?= $suggestion['ordered']->getAmount($container->quantityDecimals); ?>
                    <td><?= $suggestion['avgsales']->getAmount(1); ?>
                    <td><?= $suggestion['range_stock'] === \PHP_INT_MAX ? '' : \number_format($suggestion['range_stock'], 1); ?>
                    <td><?= $suggestion['range_reserved'] === \PHP_INT_MAX ? '' : \number_format($suggestion['range_reserved'], 1); ?>
                    <td><?= $suggestion['minstock']->getAmount($container->quantityDecimals); ?>
                    <td><?= $suggestion['minquantity']->getAmount($container->quantityDecimals); ?>
                    <td><?= $suggestion['quantitystep']->getAmount($container->quantityDecimals); ?>
                    <td<?= $class; ?>><input step="<?= $suggestion['quantitystep']->getAmount($container->quantityDecimals); ?>" type="number" value="<?= $suggestion['quantity']->getFloat($container->quantityDecimals); ?>">
                    <td><?= \number_format($suggestion['range_ordered'], 1); ?>
                    <td><?= $suggestion['range_reserved'] === \PHP_INT_MAX ? '' : $now->createModify(0, (int) \ceil($suggestion['range_ordered'] + $suggestion['range_reserved']))->format('Y-m-d') ?>
                    <td><?= $suggestion['singlePrice']->getAmount(); ?>
                    <td><?= $suggestion['totalPrice']->getAmount(); ?>
                <?php endforeach; ?>
                <?php if ($hasSupplierSwitch) : ?>
                    <tr class="highlight-7">
                        <td colspan="16"><?= $this->printHtml($suggestion['supplier']->account->name1); ?> <?= $this->printHtml($suggestion['supplier']->account->name2); ?>
                        <td><?= $subtotal->getAmount(); ?>
                <?php endif; ?>
                <tfoot>
                <tr class="highlight-3">
                    <td colspan="16">Total
                    <td><?= $total->getAmount(); ?>
            </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
        <div class="portlet highlight-2">
            <div class="portlet-body">Ordering</div>
        </div>
    </div>
</div>