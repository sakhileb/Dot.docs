<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\UserDataExportService;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        protected DeletesTeams $deletesTeams,
        protected UserDataExportService $exportService,
    )
    {
    }

    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Export user data for GDPR compliance before any data is removed.
            $this->exportService->exportUserData($user);

            // Anonymize audit trail: retain entries for security purposes but
            // strip the personal identifier (user_id) and IP address.
            AuditLog::where('user_id', $user->id)->update([
                'user_id'    => null,
                'ip_address' => '0.0.0.0',
                'user_agent' => null,
            ]);

            $this->deleteTeams($user);
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }

    /**
     * Delete the teams and team associations attached to the user.
     */
    protected function deleteTeams(User $user): void
    {
        $user->teams()->detach();

        $user->ownedTeams->each(function (Team $team) {
            $this->deletesTeams->delete($team);
        });
    }
}
