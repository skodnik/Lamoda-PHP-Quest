<?php

namespace App\Console\Commands;

use App\Models\Container;
use App\Models\Item;
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
        $headers = ['QUANTITY_CONTAINERS', 'QUANTITY_ITEMS_IN_CONTAINER', 'QUANTITY_UNIQUE_NAMES', 'QUANTITY_NAMES'];
        $rows = [[$_ENV['QUANTITY_CONTAINERS'], $_ENV['QUANTITY_ITEMS_IN_CONTAINER'], $_ENV['QUANTITY_UNIQUE_NAMES'], $_ENV['QUANTITY_NAMES']]];
        $this->table($headers, $rows);

        // Забирает все товары с уникальными именами (не unique т.к., тогда нет количества инстансов)
        $itemsOneCopy = Item::all()->groupBy(['name_id']);

        // Перебирает коллекцию и если у товара только один инстанс, то берет идентификатор контейнера из него
        // Формирует массив номеров контейнеров
        foreach ($itemsOneCopy as $item) {
            if ($item->count() === 1) {
                $containersId[] = $item[0]->container_id;
            }
        }

        // Убирает дубликаты из массива номеров контейнеров
        $containersIdOneCopy = array_unique($containersId);

        $this->info('Containers with unique items list:');
        $headers = ['id', 'name'];
        $rows = [];
        foreach ($containersIdOneCopy as $id) {
            $container = Container::where('id', $id)->first();
            $rows[] = [$id, $container->name];
        }
        $this->table($headers, $rows);

        $this->alert('Containers quantity: ' . count($containersIdOneCopy));
        $this->info('Fake get info well done');
        $this->info('End: ' . Carbon::now()->locale('en')->isoFormat('D MMMM HH:mm:ss'));
        $this->info('//////////////////////////////////////');
    }
}
