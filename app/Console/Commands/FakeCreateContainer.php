<?php

namespace App\Console\Commands;

use App\Models\Container;
use Illuminate\Console\Command;

class FakeCreateContainer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake:create_container';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Making fake container with items scope as json';

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
        $container = Container::all()->random();
        $container->items;
        dd($container->toJson());
    }
}
