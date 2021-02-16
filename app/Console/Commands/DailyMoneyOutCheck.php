<?php

namespace App\Console\Commands;

use App\Http\Controllers\SlotMoneyOutController;
use Illuminate\Console\Command;

class DailyMoneyOutCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moneyOut:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Game Money Out check';

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
        $test = new SlotMoneyOutController;
        $test->index();
    }
}
