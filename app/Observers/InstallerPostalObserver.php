<?php

namespace App\Observers;

use App\Models\InstallerPostal;
use App\Models\Postal;

class InstallerPostalObserver
{
    /**
     * Handle the InstallerPostal "created" event.
     */
    public function created(InstallerPostal $installerPostal): void
    {
        //
    }

    /**
     * Handle the InstallerPostal "updated" event.
     */
    public function updated(InstallerPostal $installerPostal): void
    {
        //
    }

    /**
     * Handle the InstallerPostal "deleted" event.
     */
    public function deleted(InstallerPostal $installerPostal): void
    {
        Postal::find($installerPostal->postal_id)->update(['installer_id' => null]);
    }

    /**
     * Handle the InstallerPostal "restored" event.
     */
    public function restored(InstallerPostal $installerPostal): void
    {
        //
    }

    /**
     * Handle the InstallerPostal "force deleted" event.
     */
    public function forceDeleted(InstallerPostal $installerPostal): void
    {
        //
    }
}
