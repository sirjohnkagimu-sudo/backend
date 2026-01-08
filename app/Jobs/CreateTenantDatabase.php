<?php

namespace App\Jobs;

use App\Models\School;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class CreateTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public School $school) {}

    public function handle()
    {
        tenancy()->create([
            'id' => $this->school->id,
        ]);

        tenancy()->initialize($this->school->id);

        Artisan::call('tenants:migrate');
    }
}
