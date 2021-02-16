<?php

namespace App\Console\Commands;

use App\Http\Controllers\DailyCalculateController;
use Illuminate\Console\Command;

class DailyRolling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DailyRollingCal:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily org Calculate for Rolling';

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
        $test = new DailyCalculateController;
        $test->rolling();
    }
}
