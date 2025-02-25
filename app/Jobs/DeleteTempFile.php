<?php

namespace App\Jobs;

use App\Configs\AppConfig;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteTempFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected string $filePath;
    protected Carbon $createdAt;
    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
        }
    }

}
