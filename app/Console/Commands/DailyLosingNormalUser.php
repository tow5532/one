<?php

namespace App\Console\Commands;

use App\Http\Controllers\DailyCalculateController;
use Illuminate\Console\Command;

class DailyLosingNormalUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DailyLosingCal:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily normal User Calculate for Losing';

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
        $test = new DailyCalculateController();
        $test->index();
    }
}
