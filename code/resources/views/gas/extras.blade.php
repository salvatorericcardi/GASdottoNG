<x-larastrap::accordion always_open="true">
    <x-larastrap::accordionitem :label="_i('Luoghi di Consegna')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'deliveries.base-edit',
                    'typename' => 'delivery',
                    'typename_readable' => _i('Luogo di Consegna'),
                    'targeturl' => 'deliveries'
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'delivery-list',
                    'items' => $currentgas->deliveries,
                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>Aggiungendo elementi verrà attivata la possibilità per ogni utente di selezionare il proprio luogo di consegna preferito, nei documenti di riassunto degli ordini le prenotazioni saranno suddivise per luogo, e sarà possibile attivare alcuni ordini solo per gli utenti afferenti a determinati luoghi di consegna: utile per GAS distribuiti sul territorio.')
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label="_i('File Condivisi')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'attachment.base-edit',
                    'typename' => 'attachment',
                    'target_update' => 'attachment-list-' . $gas->id,
                    'typename_readable' => _i('File'),
                    'targeturl' => 'attachments',
                    'extra' => [
                        'target_type' => 'App\Gas',
                        'target_id' => $gas->id
                    ]
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'attachment-list-' . $gas->id,
                    'items' => $gas->attachments,
                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>I files qui aggiunti saranno accessibili a tutti gli utenti dalla dashboard: utile per condividere documenti di interesse comune.')
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label="_i('Aliquote IVA')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'vatrates.base-edit',
                    'typename' => 'vatrate',
                    'typename_readable' => _i('Aliquota IVA'),
                    'targeturl' => 'vatrates'
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'vatrate-list',
                    'items' => App\VatRate::orderBy('name', 'asc')->get(),
                    'empty_message' => _i("Non ci sono elementi da visualizzare.<br/>Le aliquote potranno essere assegnate ai diversi prodotti nei listini dei fornitori, e vengono usate per scorporare automaticamente l'IVA dai totali delle fatture caricate in <strong>Contabilità -> Fatture</strong>.")
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label="_i('Modificatori')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'modifiertype.base-edit',
                    'typename' => 'modtype',
                    'typename_readable' => _i('Modificatore'),
                    'targeturl' => 'modtypes'
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'modtype-list',
                    'items' => App\ModifierType::where('hidden', false)->orderBy('name', 'asc')->get(),
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    @if(env('GASDOTTO_NET', false))
        <x-larastrap::accordionitem :label="_i('Log E-Mail')">
            <?php $logs = App\InnerLog::where('type', 'mail')->orderBy('created_at', 'desc')->take(10)->get() ?>

            <div class="row">
                <div class="col">
                    @if($logs->isEmpty())
                        <div class="alert alert-info">
                            {{ _i('Non ci sono log relativi alle email.') }}
                        </div>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="30%">{{ _i('Data') }}</th>
                                    <th width="70%">{{ _i('Messaggio') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>{{ printableDate($log->created_at) }}</td>
                                        <td>{{ $log->message }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </x-larastrap::accordionitem>
    @endif
</x-larastrap::accordion>
