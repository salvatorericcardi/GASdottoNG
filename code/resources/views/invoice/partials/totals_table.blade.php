<?php

$orders_total_taxable = 0;
$orders_total_tax = 0;
$orders_total = 0;
$orders_modifiers = [];
$orders_other_modifiers = [];
$calculated_summaries = [];

foreach($invoice->orders as $o) {
    $summary = $o->calculateInvoicingSummary();
    $calculated_summaries[$o->id] = $summary;
    $orders_total_taxable += $summary->total_taxable;
    $orders_total_tax += $summary->total_tax;
    $orders_total += $summary->total_taxable + $summary->total_tax;

    $modifiers = $o->applyModifiers(null, false);

    $modifiers_good = $modifiers->filter(function($value, $key) {
        return is_null($value->modifier->movementType);
    });

    $aggregated_modifiers = App\ModifiedValue::aggregateByType($modifiers_good);

    foreach($aggregated_modifiers as $am_id => $am) {
        if (!isset($orders_modifiers[$am_id])) {
            $orders_modifiers[$am_id] = $am;
        }
        else {
            $orders_modifiers[$am_id]->amount += $am->amount;
        }

        $orders_total += $am->amount;
    }

    $modifiers_bad = $modifiers->filter(function($value, $key) {
        return is_null($value->modifier->movementType) == false;
    });

    $aggregated_modifiers = App\ModifiedValue::aggregateByType($modifiers_bad);

    foreach($aggregated_modifiers as $am_id => $am) {
        if (!isset($orders_other_modifiers[$am_id])) {
            $orders_other_modifiers[$am_id] = $am;
        }
        else {
            $orders_other_modifiers[$am_id]->amount += $am->amount;
        }
    }
}

?>

<div class="simple-sum-container">
    <table class="table table-borderless">
        <thead>
            <tr>
                <th width="30%"></th>
                <th width="35%">{{ _i('Fattura') }}</th>
                <th width="35%">{{ _i('Ordini Coinvolti') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ _i('Totale Imponibile') }}</td>
                <td>
                    <x-larastrap::price name="total" classes="simple-sum" :required="$editable" :disabled="$editable == false" squeeze autocomplete="off" />
                </td>
                <td>
                    <x-larastrap::price disabled squeeze autocomplete="off" :value="$orders_total_taxable" />
                </td>
            </tr>
            <tr>
                <td>{{ _i('Totale IVA') }}</td>
                <td>
                    <x-larastrap::price name="total_vat" classes="simple-sum" :required="$editable" :disabled="$editable == false" squeeze autocomplete="off" />
                </td>
                <td>
                    <x-larastrap::price disabled squeeze autocomplete="off" :value="$orders_total_tax" />
                </td>
            </tr>

            @foreach($orders_modifiers as $om)
                <tr>
                    <td>{{ $om->name }}</td>
                    <td>&nbsp;</td>
                    <td>
                        <x-larastrap::price disabled squeeze autocomplete="off" :value="$om->amount" />
                    </td>
                </tr>
            @endforeach

            <tr>
                <td>{{ _i('Totale') }}</td>
                <td>
                    <x-larastrap::price classes="simple-sum-result" disabled squeeze autocomplete="off" :value="$invoice->total + $invoice->total_vat" />
                </td>
                <td>
                    <x-larastrap::price disabled squeeze autocomplete="off" :value="$orders_total" />
                </td>
            </tr>

            @if(empty($orders_other_modifiers) == false)
                <tr class="border-top">
                    <td colspan="3">{{ _i('Altri modificatori non destinati a questa fattura:') }}</td>
                </tr>

                @foreach($orders_other_modifiers as $om)
                    <tr>
                        <td>{{ $om->name }}</td>
                        <td>&nbsp;</td>
                        <td>
                            <x-larastrap::price disabled squeeze autocomplete="off" :value="$om->amount" />
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
