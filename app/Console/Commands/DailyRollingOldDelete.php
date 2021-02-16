<?php

namespace App\Console\Commands;

use App\Http\Controllers\DailyCalculateDelController;
use Illuminate\Console\Command;

class DailyRollingOldDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DailyRollingDel:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Rolling for old data is Delete';

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
     * @return mixed
     */
    public function handle()
    {
        $test = new DailyCalculateDelController;
        $test->start();
    }
}
