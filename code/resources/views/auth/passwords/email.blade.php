@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3 mt-3">
    <x-larastrap::form method="POST" :action="route('password.email')" :buttons="[['color' => 'success', 'type' => 'submit', 'label' => _i('Chiedi Reset Password')]]">
        <x-larastrap::text name="username" :label="_i('Username o Indirizzo E-Mail')" />
    </x-larastrap::form>
</div>

<div class="col-12 col-md-6 offset-md-3">
    <hr/>
    <p>
        <a href="{{ route('login') }}">{{ _i('Login') }}</a>
    </p>
</div>

@endsection
