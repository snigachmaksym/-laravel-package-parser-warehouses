<?php

namespace Parser\Postal\Commands;

use Illuminate\Console\Command;
use Parser\Postal\Services\PostalServices\Delivery;
use Parser\Postal\Services\PostalServices\InTime;

class ParserPostal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:postal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing postal branches';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    private $postal;

    /**
     * ChangeButtonStatus constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    function handle()
    {
        echo '/** start parsing */';
        (new Delivery())->saveData();
//        (new InTime())->saveData();
        logger()->info('/** start parsing */', [config('parser-postal.intime-api-key')]);
    }
}
