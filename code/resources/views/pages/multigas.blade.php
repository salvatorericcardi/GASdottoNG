@extends('app')

@section('content')

@if(count($groups) > 1)
    <div class="row">
        <div class="col">
            <a class="btn btn-light" disabled>{{ _i('GAS attivo: %s', [$currentgas->name]) }}</a>
            @foreach($groups as $gas)
                @if($gas->id != $currentgas->id)
                    <a href="{{ route('multigas.goto', $gas->id) }}" class="btn btn-primary">Passa a {{ $gas->name }}</a>
                @endif
            @endforeach
        </div>
    </div>

    <hr>
@endif

<x-larastrap::tabs>
    <x-larastrap::tabpane :label="_i('GAS')" active="true">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'multigas.base-edit',
                    'typename' => 'gas',
                    'typename_readable' => _i('GAS'),
                    'targeturl' => 'multigas'
                ])
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'gas-list',
                    'items' => $groups,
                    'url' => url('multigas'),
                ])
            </div>
        </div>
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('Fornitori')">
        <div class="row">
            <div class="col">
                <table class="table multigas-editor">
                    <thead>
                        <tr>
                            <th></th>

                            @foreach($groups as $gas)
                                <th>
                                    {{ $gas->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Supplier::orderBy('name', 'asc')->get() as $supplier)
                            <tr>
                                <td>{{ $supplier->name }}</td>

                                @foreach($groups as $gas)
                                    <td>
                                        <input type="checkbox" data-gas="{{ $gas->id }}" data-target-type="supplier" data-target-id="{{ $supplier->id }}" {{ $gas->suppliers()->where('suppliers.id', $supplier->id)->first() != null ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('Ordini')">
        <div class="row">
            <div class="col">
                <table class="table multigas-editor">
                    <thead>
                        <tr>
                            <th></th>

                            @foreach($groups as $gas)
                                <th>
                                    {{ $gas->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Aggregate::whereHas('orders', function($query) {
                            $query->whereIn('status', ['open', 'closed', 'suspended']);
                        })->orderBy('id', 'asc')->get() as $aggregate)
                            <tr>
                                <td>{{ $aggregate->printableName() }}</td>

                                @foreach($groups as $gas)
                                    <td>
                                        @include('multigas.aggregate', ['gas' => $gas, 'aggregate' => $aggregate])
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-larastrap::tabpane>
</x-larastrap::tabs>

@endsection
