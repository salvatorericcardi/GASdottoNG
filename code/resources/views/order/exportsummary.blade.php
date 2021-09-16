<x-larastrap::modal :title="_i('Riassunto Prodotti')" classes="close-on-submit order-document-download-modal">
    <x-larastrap::form method="GET" :action="url('orders/document/' . $order->id . '/summary')" label_width="2" input_width="10">
        <p>
            {{ ("Da qui puoi ottenere un documento che riassume le quantità prenotate di tutti i prodotti: utile da inviare al fornitore, una volta chiuso l'ordine.") }}
        </p>
        <p>
            {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
        </p>

        <hr/>

        @if($currentgas->hasFeature('shipping_places'))
            <?php

            $options = [
                'all_by_name' => _i('Tutti (ordinati per utente)'),
                'all_by_place' => _i('Tutti (ordinati per luogo)'),
            ];

            foreach($currentgas->deliveries as $delivery) {
                $options[$delivery->id] = $delivery->name;
            }

            ?>
            <x-larastrap::radios name="shipping_place" :label="_i('Luogo di Consegna')" :options="$options" value="all_by_place" />
        @endif

        <?php list($options, $values) = flaxComplexOptions(App\Order::formattableColumns('summary')) ?>
        <x-larastrap::checks name="fields" :label="_i('Colonne')" :options="$options" :value="$values" />

        <x-larastrap::radios name="status" :label="_i('Quantità')" :options="['booked' => _i('Prenotate'), 'delivered' => _i('Consegnate')]" value="booked" />
        <x-larastrap::radios name="format" :label="_i('Formato')" :options="['pdf' => _i('PDF'), 'csv' => _i('CSV'), 'gdxp' => _i('GDXP')]" value="pdf" />

        @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
    </x-larastrap::form>
</x-larastrap::modal>
