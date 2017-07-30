<?php

if ($obj == null)
    $obj = $default;

if (!isset($dom_id))
    $dom_id = rand();

if (!isset($editable))
    $editable = false;

?>

<div class="modal fade" id="editMovement-{{ $dom_id }}" tabindex="-1" role="dialog" aria-labelledby="editMovement-{{ $dom_id }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal creating-form" method="POST" action="{{ url('movements') }}" data-toggle="validator">
                <input type="hidden" name="update-field" value="movement-id-{{ $dom_id }}">
                <input type="hidden" name="update-field" value="movement-date-{{ $dom_id }}">
                <input type="hidden" name="close-modal" value="">
                <input type="hidden" name="post-saved-function" value="refreshFilter">

                @if(isset($extra))
                    @foreach($extra as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                @endif

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modifica Movimento</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="{{ $obj->id }}">
                    <input type="hidden" name="type" value="{{ $obj->type }}" />
                    <input type="hidden" name="sender_type" value="{{ $obj->sender_type }}" />
                    <input type="hidden" name="sender_id" value="{{ $obj->sender_id }}" />
                    <input type="hidden" name="target_type" value="{{ $obj->target_type }}" />
                    <input type="hidden" name="target_id" value="{{ $obj->target_id }}" />

                    @include('commons.decimalfield', [
                        'obj' => $obj,
                        'name' => 'amount',
                        'label' => 'Valore',
                        'postlabel' => '€',
                        'fixed_value' => $editable ? false : $obj->amount
                    ])

                    <div class="col-sm-{{ $fieldsize }} col-sm-offset-{{ $labelsize }}">
                        @if($obj->sender && array_search('App\CreditableTrait', class_uses($obj->sender)) !== false)
                            <p>
                                {{ $obj->sender->printableName() }}: {{ $obj->sender->balance }} €
                            </p>
                        @endif

                        @if($obj->target && array_search('App\CreditableTrait', class_uses($obj->target)) !== false)
                            <p>
                                {{ $obj->target->printableName() }}: {{ $obj->target->balance }} €
                            </p>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="method" class="col-sm-{{ $labelsize }} control-label">Metodo</label>
                        <div class="col-sm-{{ $fieldsize }}">
                            <div class="btn-group" data-toggle="buttons">
                                @foreach($obj->valid_payments as $method_id => $info)
                                    <label class="btn btn-primary {{ $obj->method == $method_id ? 'active' : '' }}">
                                        <input type="radio" name="method" value="{{ $method_id }}" autocomplete="off" {{ $obj->method == $method_id ? 'checked' : '' }}> {{ $info->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @include('commons.datefield', [
                        'obj' => $obj,
                        'name' => 'registration_date',
                        'label' => 'Data',
                        'defaults_now' => true
                    ])

                    @include('commons.textfield', [
                        'obj' => $obj,
                        'name' => 'identifier',
                        'label' => 'Identificativo'
                    ])

                    @include('commons.textarea', [
                        'obj' => $obj,
                        'name' => 'notes',
                        'label' => 'Note'
                    ])
                </div>

                <div class="modal-footer">
                    @if($editable)
                        <button type="button" class="btn btn-danger spare-delete-button password-protected" data-delete-url="{{ url('movements/' . $obj->id) }}">Elimina</button>
                    @endif

                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
