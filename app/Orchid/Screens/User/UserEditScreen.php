<?php

namespace App\Orchid\Screens\User;

use App\Models\User;
use Orchid\Screen\Screen;
use App\Orchid\Layouts\UserEditLayout; // Importa il nuovo layout
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Orchid\Access\Impersonation;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class UserEditScreen extends Screen
{
    public $name = 'Edit User';

    public function query(User $user): array
    {
        return [
            'user' => $user,
        ];
    }

    public function layout(): array
    {
        return [
            UserEditLayout::class, // Usa il nuovo layout
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make(__('Impersonate user'))
                ->icon('bg.box-arrow-in-right')
                ->confirm(__('You can revert to your original state by logging out.'))
                ->method('loginAs'),

            Button::make('Save')
                ->icon('check')
                ->method('save'), // Nome del metodo da chiamare al clic del pulsante

            Button::make('Cancel')
                ->icon('ban')
                ->method('cancel'), // Nome del metodo da chiamare al clic del pulsante
        ];
    }


    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(User $user, Request $request)
    {
        $request->validate([
            /*'user.email' => [
                'required',
                Rule::unique(User::class, 'email')->ignore($user),
            ],*/
        ]);

        $permissions = collect($request->get('permissions'))
            ->map(fn ($value, $key) => [base64_decode($key) => $value])
            ->collapse()
            ->toArray();

        $user->when($request->filled('user.password'), function (Builder $builder) use ($request) {
            $builder->getModel()->password = Hash::make($request->input('user.password'));
        });

        $user
            ->fill($request->collect('user')->except(['password', 'permissions', 'roles'])->toArray())
            ->forceFill(['permissions' => $permissions])
            ->save();

        $user->replaceRoles($request->input('user.roles'));

        Toast::info(__('User was saved.'));

        return redirect()->route('platform.systems.users');
    }

    public function loginAs(User $user)
    {

        if ($user->roles->contains('id', 1)) {
            if (auth()->user()->roles->doesntContain('id', 1)) {
                Toast::error(__('You do not have permission to impersonate an admin user.'));
                return redirect()->route(config('platform.index'));
            }
        }

        Impersonation::loginAs($user);

        Toast::info(__('You are now impersonating this user'));

        return redirect()->route(config('platform.index'));


            
    }
}
