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

use Modules\Purchase\Models\OrderSuggestion\OrderSuggestionStatus;
use phpOMS\Stdlib\Base\FloatInt;
use phpOMS\Stdlib\Base\SmartDateTime;
use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View $this
 */
echo $this->data['nav']->render();
?>
<?php if ($this->data['suggestion']->status === OrderSuggestionStatus::DRAFT) : ?>
<div class="row">
    <div class="col-xs-12 col-sm-7 col-md-5 col-lg-4">
        <div class="portlet">
            <div class="portlet-body">
                <input type="hidden" name="id" form="suggestionList" value="<?= $this->data['suggestion']->id; ?>">
                <input name="save" type="submit" form="suggestionList" value="<?= $this->getHtml('Save', '0', '0'); ?>">
                <!--<input name="order" type="submit" form="suggestionList" formaction="<?= UriFactory::build('{/api}purchase/order/suggestion/bill'); ?>" formmethod="put" value="<?= $this->getHtml('Order'); ?>">-->
                <input name="delete" class="cancel" type="submit" form="suggestionList" formmethod="delete" value="<?= $this->getHtml('Delete', '0', '0'); ?>">
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Suggestions'); ?><i class="g-icon download btn end-xs">download</i></div>
            <div class=""><!-- @todo Re-implement slider once we figured out how to combine slider+sticky -->
            <table id="suggestionList" class="default sticky" data-tag="form"
                data-uri="<?= UriFactory::build('{/api}purchase/order/suggestion'); ?>" data-method="post"
                data-redirect="<?= UriFactory::build('{/base}/purchase/order/suggestion/view?id=' . $this->data['suggestion']->id); ?>">
                <thead>
                <tr>
                    <td><?= $this->getHtml('Item'); ?>
                    <td class="wf-100">
                    <td><?= $this->getHtml('Supplier'); ?>
                    <td><?= $this->getHtml('Stock'); ?>
                    <td><?= $this->getHtml('Reserved'); ?>
                    <td><?= $this->getHtml('Ordered'); ?>
                    <td><?= $this->getHtml('AvgConsumption'); ?>
                    <td><?= $this->getHtml('Range1'); ?>
                    <td><?= $this->getHtml('Range2'); ?>
                    <td><?= $this->getHtml('MinStock'); ?>
                    <td><?= $this->getHtml('MinOrder'); ?>
                    <td><?= $this->getHtml('Steps'); ?>
                    <td style="min-width: 75px;"><?= $this->getHtml('Ordering'); ?>
                    <td><?= $this->getHtml('NewRange'); ?>
                    <td><?= $this->getHtml('Price'); ?>
                    <td><?= $this->getHtml('Costs'); ?>
                <tbody>
                <?php
                    $now = new SmartDateTime('now');
                    $total = new FloatInt();
                    $subtotal = new FloatInt();
                    $lastSupplier = 0;

                    $supplier = $this->request->getDataString('supplier');
                    $hasSupplierSwitch = false;

                    $isFirst = true;

                foreach ($this->data['suggestion']->elements as $element) :
                    $isNew = $now->getTimestamp() - $element->item->createdAt->getTimestamp() < 60 * 60 * 24 * 60;

                    $total->add($element->costs);
                    $container = \reset($element->item->container);

                    $class = '';
                    if ($element->quantity->value !== 0) {
                        $class = ' class="hl-2"';
                    }
                ?>
                <?php
                    if (empty($supplier) && $lastSupplier !== $element->supplier->id && !$isFirst) :
                        $hasSupplierSwitch = true;
                        $lastSupplier = $element->supplier->id;
                ?>
                    <tr class="hl-7">
                        <td colspan="15"><?= $this->printHtml($element->supplier->account->name1); ?> <?= $this->printHtml($element->supplier->account->name2); ?>
                        <td><?= $subtotal->getAmount(); ?>
                <?php
                    $subtotal = new FloatInt();
                    endif;

                    $subtotal->add($element->costs);
                    $isFirst = false;
                ?>
                <tr data-name="element" data-value="<?= $element->id; ?>">
                    <td><?= $this->printHtml($element->item->number); ?>
                    <td><?= $this->printHtml($element->item->getL11n('name1')->content); ?> <?= $this->printHtml($element->item->getL11n('name2')->content); ?>
                    <td><?= $this->printHtml($element->supplier->number); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['stock']->getAmount($container->quantityDecimals); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['reserved']->getAmount($container->quantityDecimals); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['ordered']->getAmount($container->quantityDecimals); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['avgsales']->getAmount(1); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['range_stock'] === \PHP_INT_MAX ? '' : \number_format($this->data['suggestion_data'][$element->item->id]['range_stock'], 1); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['range_reserved'] === \PHP_INT_MAX ? '' : \number_format($this->data['suggestion_data'][$element->item->id]['range_reserved'], 1); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['minstock']->getAmount($container->quantityDecimals); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['minquantity']->getAmount($container->quantityDecimals); ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['quantitystep']->getAmount($container->quantityDecimals); ?>
                    <td<?= $class; ?>><input name="quantity"
                        step="<?= $this->data['suggestion_data'][$element->item->id]['quantitystep']->getAmount($container->quantityDecimals); ?>"
                        type="number"
                        value="<?= $element->quantity->getFloat($container->quantityDecimals); ?>">
                    <td><?= $this->data['suggestion_data'][$element->item->id]['range_reserved'] === \PHP_INT_MAX
                        ? ''
                        : $now->createModify(
                            0,
                            (int) ($months = ($this->data['suggestion_data'][$element->item->id]['range_ordered']
                                + $this->data['suggestion_data'][$element->item->id]['range_reserved'])),
                            (int) (($months - ((int) $months)) * 30))
                            ->format('Y-m-d')
                        ?>
                    <td><?= $this->data['suggestion_data'][$element->item->id]['singlePrice']->getAmount(); ?>
                    <td><?= $element->costs->getAmount(); ?>
                <?php endforeach; ?>
                <?php if (empty($supplier)) : ?>
                    <tr class="hl-7">
                        <td colspan="15"><?= $this->printHtml($element->supplier->account->name1); ?> <?= $this->printHtml($element->supplier->account->name2); ?>
                        <td><?= $subtotal->getAmount(); ?>
                <?php
                    $subtotal = new FloatInt();
                    endif;
                ?>
                <tfoot>
                <tr class="hl-3">
                    <td colspan="15"><?= $this->getHtml('Total'); ?>
                    <td><?= $total->getAmount(); ?>
            </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
        <div class="portlet hl-2">
            <div class="portlet-body"><?= $this->getHtml('Ordering'); ?></div>
        </div>
    </div>
</div>