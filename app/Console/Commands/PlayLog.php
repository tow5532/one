<?php

namespace App\Console\Commands;

use App\Http\Controllers\GetGameLogDataController;
use Illuminate\Console\Command;

class PlayLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Playlog:get';

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
     * @return mixed
     */
    public function handle()
    {
        $test = new GetGameLogDataController();
        $test->index();
    }
}
