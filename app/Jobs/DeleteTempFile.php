<?php

namespace App\Jobs;

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
    public function __construct(string $filePath, string $createdAt)
    {
        $this->filePath = $filePath;
        $this->createdAt = Carbon::parse($createdAt);
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Delete only if file is older than 12 hours
        if ($this->createdAt->addHours(12)->isPast()) {
            // File deletion
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            // Directory deletion
            } elseif (Storage::directoryExists($this->filePath)) {
                Storage::deleteDirectory($this->filePath);
            }
        }
    }
}
