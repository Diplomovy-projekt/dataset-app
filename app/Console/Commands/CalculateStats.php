<?php

namespace App\Console\Commands;

use App\Models\DatasetStatistics;
use Illuminate\Console\Command;

class CalculateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting statistics recalculation...');

        DatasetStatistics::recalculateAllStatistics();

        $this->info('Statistics recalculation completed successfully!');

        return Command::SUCCESS;
    }
}
