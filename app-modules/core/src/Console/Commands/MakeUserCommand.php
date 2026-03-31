<?php

namespace Metafori\Core\Console\Commands;

use Filament\Commands\MakeUserCommand as FilamentMakeUserCommand;
use Metafori\Core\Enums\Role;
use Metafori\Core\Models\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\multiselect;

#[AsCommand(name: 'core:make:user')]
class MakeUserCommand extends FilamentMakeUserCommand
{
    protected $description = 'Create a new user with roles';

    protected $name = 'core:make:user';

    protected $aliases = [];

    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $options[] = new InputOption(
            name: 'roles',
            shortcut: null,
            mode: InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            description: 'The roles to assign to the user',
        );

        return $options;
    }

    protected function createUser(): User
    {
        $roles = $this->option('roles') ?: multiselect(
            label: 'Select roles for the user',
            options: collect(Role::cases())->map->value,
            required: false,
        );

        return parent::createUser()
            ->assignRole($roles);
    }
}
