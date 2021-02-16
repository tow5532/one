<?php

namespace App\Console\Commands;

use App\Http\Controllers\DailyCalculateTotalController;
use Illuminate\Console\Command;

class DailyLosingSubTotal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DailyLosingCal:sub';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Sub-Company User Calculate for Losing';

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
        $test = new DailyCalculateTotalController;
        $test->index('sub_company');
    }
}
