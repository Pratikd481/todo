<?php

namespace App\Console\Commands;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:old-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        //dd(Carbon::now()->subMonth(1)->toDateTimeString());
        $tasks = Task::withTrashed()->where(
            'deleted_at',
            '<',
            Carbon::now()->subMonth(1)->toDateTimeString()
        );
        $tasks->forceDelete();
        return Command::SUCCESS;
    }
}
