<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Select;
use App\Orchid\Models\Role;

class UserEditLayout extends Rows
{
    // Definisci i campi per il layout
    public function fields(): array
    {
        return [
            Select::make('user.roles')
                ->fromModel(Role::class, 'name')
                ->multiple()
                ->title('User Roles'),
        ];
    }
}
