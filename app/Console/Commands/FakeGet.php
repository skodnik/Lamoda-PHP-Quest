<?php

namespace App\Console\Commands;

use App\Models\Container;
use App\Models\Item;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FakeGet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get containers with unique item names';

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
        $this->info('//////////////////////////////////////');
        $this->info('Fake get info');
        $this->info('Start: ' . Carbon::now()->locale('en')->isoFormat('D MMMM HH:mm:ss'));
        $this->info('Fake init params:');
        $headers = ['QUANTITY_CONTAINERS', 'QUANTITY_ITEMS_IN_CONTAINER', 'QUANTITY_UNIQUE_NAMES'];
        $rows = [[$_ENV['QUANTITY_CONTAINERS'], $_ENV['QUANTITY_ITEMS_IN_CONTAINER'], $_ENV['QUANTITY_UNIQUE_NAMES']]];
        $this->table($headers, $rows);

        // Выбирает контейнеры с уникальными товарами
        $containers = Service::getContainersWithUniqueItems();

        // Форматирование отображения результата
        $this->info('Containers with unique items list:');
        $headers = ['id', 'name'];
        $rows = [];
        $names = [];

        foreach ($containers as $id) {
            $container = Container::where('id', $id)->first();
            $rows[] = [$id, $container->name];

            $items = $container->items;

            foreach ($items as $item) {
                $names[$item->name_id][] = $container->id;
            }
        }
        $this->table($headers, $rows);

        $this->alert('Containers quantity: ' . count($containers) . ', unique names quantity: ' . count($names));
        $this->info('Fake get info well done');
        $this->info('End: ' . Carbon::now()->locale('en')->isoFormat('D MMMM HH:mm:ss'));
        $this->info('//////////////////////////////////////');
    }
}
