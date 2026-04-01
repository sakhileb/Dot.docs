<?php

namespace App\Providers;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteTeamMember;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::createTeamsUsing(CreateTeam::class);
        Jetstream::updateTeamNamesUsing(UpdateTeamName::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::removeTeamMembersUsing(RemoveTeamMember::class);
        Jetstream::deleteTeamsUsing(DeleteTeam::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions([
            'read',
            'documents:read',
            'comments:read',
        ]);

        Jetstream::role('admin', 'Administrator', [
            'documents:create',
            'documents:read',
            'documents:update',
            'documents:delete',
            'comments:create',
            'comments:read',
            'comments:resolve',
            'shares:create',
            'shares:manage',
        ])->description('Administrator users can perform any action.');

        Jetstream::role('editor', 'Editor', [
            'documents:create',
            'documents:read',
            'documents:update',
            'comments:create',
            'comments:read',
            'comments:resolve',
            'shares:create',
        ])->description('Editors can create and update documents and collaborate on comments.');

        Jetstream::role('commenter', 'Commenter', [
            'documents:read',
            'comments:create',
            'comments:read',
        ])->description('Commenters can review and discuss documents without editing content.');

        Jetstream::role('viewer', 'Viewer', [
            'documents:read',
            'comments:read',
        ])->description('Viewers can access shared content in read-only mode.');

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
            'documents:create',
            'documents:read',
            'documents:update',
            'documents:delete',
            'comments:create',
            'comments:read',
            'comments:resolve',
            'shares:create',
            'shares:manage',
        ]);
    }
}
