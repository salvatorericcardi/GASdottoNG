<?php $combos = $product->variantCombos ?>

@if($combos->isEmpty() == false)
    <hr>

    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        @foreach($combos->first()->values as $value)
                            <th>{{ $value->variant->name }}</th>
                        @endforeach

                        <th width="25%">Differenza Prezzo</th>

                        @if ($product->measure->discrete)
                            <th width="25%">Differenza Peso</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->variantCombos as $combo)
                        <tr>
                            @foreach($combo->values as $value)
                                <td>{{ $value->value }}</td>
                            @endforeach

                            <td>{{ printablePriceCurrency($combo->price_offset) }}</td>

                            @if ($product->measure->discrete)
                                <td>{{ $combo->weight_offset }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif