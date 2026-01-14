<?php

namespace App\Observers;
use App\Models\User;
use App\Models\Client;
use Filament\Notifications\Notification;

class ClientObserver
{
    public function creating(Client $client): void
    {
        // Set IP address
        $client->ip = request()->ip();
    }
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        $email = config('variables.setting.admin_email');
        $admin = User::where('email', $email)->first();

        if ($admin) {
            Notification::make()
                ->title('New Client Registered')
                ->body("{$client->name} just registered.")
                ->sendToDatabase($admin);
        }
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "restored" event.
     */
    public function restored(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "force deleted" event.
     */
    public function forceDeleted(Client $client): void
    {
        //
    }
}
