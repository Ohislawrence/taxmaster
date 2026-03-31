<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\User;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use App\Models\BusinessInvitation;


class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]), function (User $user) use ($input) {
                $this->createTeam($user);
                // Assign business role to new user
                $user->assignRole('business');

                // Dispatch welcome email with 24-hour delay
                SendWelcomeEmail::dispatch($user)->delay(now()->addHours(24));

                // If the registration contains an invite token, verify it and assign business ownership
                if (!empty($input['invite'])) {
                    try {
                        $hash = hash('sha256', $input['invite']);

                        $invite = BusinessInvitation::where('token', $hash)
                            ->where('email', strtolower($input['email']))
                            ->whereNull('used_at')
                            ->where(function ($q) {
                                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                            })
                            ->latest()
                            ->first();

                        if ($invite && $invite->business) {
                            $business = $invite->business;
                            $business->owner_id = $user->id;
                            $business->save();

                            $invite->used_at = now();
                            $invite->accepted_by = $user->id;
                            $invite->save();
                        }
                    } catch (\Throwable $e) {
                        // Don't fail registration on invite processing errors; log for debugging
                        \Illuminate\Support\Facades\Log::error('Invite acceptance failed: ' . $e->getMessage());
                    }
                }
            });
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}
