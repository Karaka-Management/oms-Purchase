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

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View $this
 */
echo $this->data['nav']->render();
?>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('OrderSuggestions'); ?><i class="g-icon download btn end-xs">download</i></div>
            <div class="slider">
            <table id="suggestionList" class="default sticky">
                <thead>
                <tr>
                    <td><?= $this->getHtml('ID', '0', '0'); ?>
                        <label for="suggestionList-sort-1">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-1">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="suggestionList-sort-2">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-2">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <td><?= $this->getHtml('Status'); ?>
                        <label for="suggestionList-sort-9">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-9">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="suggestionList-sort-10">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-10">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <td><?= $this->getHtml('Created'); ?>
                        <label for="suggestionList-sort-5">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-5">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="suggestionList-sort-6">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-6">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Creator'); ?>
                        <label for="suggestionList-sort-3">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-3">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="suggestionList-sort-4">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-4">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <td><?= $this->getHtml('Costs'); ?>
                        <label for="suggestionList-sort-5">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-5">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="suggestionList-sort-6">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-6">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Elements'); ?>
                        <label for="suggestionList-sort-7">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-7">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="suggestionList-sort-8">
                            <input type="radio" name="suggestionList-sort" id="suggestionList-sort-8">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                <tbody>
                <?php $count = 0; foreach ($this->data['suggestions'] as $key => $value) :
                    ++$count;
                    $url = UriFactory::build('{/base}/purchase/order/suggestion/view?{?}&id=' . $value->id);
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><a href="<?= $url; ?>"><?= $value->id; ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->getHtml(':SuggestionStatus-' . $value->status); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->createdAt->format('Y-m-d'); ?></a>
                        <td><a class="content" href="<?= UriFactory::build('{/base}/profile/view?{?}&for=' . $value->createdBy->id); ?>">
                                <?= $this->printHtml($this->renderUserName(
                                    '%3$s %2$s %1$s',
                                    [
                                        $value->createdBy->name1,
                                        $value->createdBy->name2,
                                        $value->createdBy->name3,
                                        $value->createdBy->login ?? '',
                                    ])
                                ); ?>
                            </a>
                        <td><a href="<?= $url; ?>"><?= $value->getTotalCosts()->getAmount(); ?></a>
                        <td><a href="<?= $url; ?>"><?= \count($value->elements); ?></a>
                <?php endforeach; ?>
                <?php if ($count === 0) : ?>
                    <tr><td colspan="12" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                <?php endif; ?>
            </table>
            </div>
        </div>
    </div>
</div>
