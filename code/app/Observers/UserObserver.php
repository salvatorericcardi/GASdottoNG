<?php

namespace App\Observers;

use App\Exceptions\IllegalArgumentException;

use App\User;
use App\Role;
use App\Delivery;

class UserObserver
{
    private function checkUsername($user)
    {
        /*
            Lo username deve essere univoco per tutti gli utenti di tutti i GAS
        */
        $test = User::withTrashed()->withoutGlobalScopes()->where('id', '!=', $user->id)->where('username', $user->username)->first();
        if ($test != null) {
            throw new IllegalArgumentException(_i('Username già assegnato'), 'username');
        }
    }

    private function checkFirstLastName($user)
    {
        if (filled($user->firstname) && filled($user->lastname)) {
            $test = User::where('id', '!=', $user->id)->where('firstname', $user->firstname)->where('lastname', $user->lastname)->first();
            if ($test != null) {
                throw new IllegalArgumentException(_i('Nome e cognome già presenti'), 'lastname');
            }
        }
    }

    public function creating(User $user)
    {
        $this->checkUsername($user);
        $this->checkFirstLastName($user);
    }

    private function createdFriend($user)
    {
        $default_role = $user->gas->roles['friend'] ?? -1;
        $role = Role::find($default_role);

        if (is_null($role)) {
            $default_role = $user->gas->roles['user'];
            $role = Role::find($default_role);
        }

        if ($role) {
            $user->addRole($role, $user->gas);
        }

        $user->preferred_delivery_id = '0';
        return $user;
    }

    private function createdNormal($user)
    {
        $default_role = $user->gas->roles['user'];
        $role = Role::find($default_role);
        if ($role) {
            $user->addRole($role, $user->gas);
        }

        $fallback_delivery = Delivery::where('default', true)->first();
        if ($fallback_delivery != null) {
            $user->preferred_delivery_id = $fallback_delivery->id;
        }

        return $user;
    }

    public function created(User $user)
    {
        if ($user->isFriend()) {
            $user = $this->createdFriend($user);
        }
        else {
            $user = $this->createdNormal($user);
        }

        $user->save();
    }

    public function updating(User $user)
    {
        /*
            Nota: qui deliberatamente non controllo l'univocità di nome e
            cognome. Questa regola è stata introdotta ad un certo punto, quando
            già c'erano numerosi utenti omonimi sulle istanze, forzando qui tale
            controllo genera grattacapi in tutti i casi in cui l'utente non
            univoco viene salvato in qualsiasi circostanza
        */

        $this->checkUsername($user);

        if (filled($user->card_number)) {
            $test = User::where('id', '!=', $user->id)->where('gas_id', $user->gas_id)->where('card_number', $user->card_number)->count();
            if ($test != 0) {
                throw new IllegalArgumentException(_i('Numero tessera già assegnato'), 'card_number');
            }
        }
    }
}
