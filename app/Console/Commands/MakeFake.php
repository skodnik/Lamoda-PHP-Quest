<?php

namespace App\Console\Commands;

use App\Models\Container;
use App\Models\Name;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MakeFake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fake items and containers maker';

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
        $this->info('Fake maker init');
        $this->info('Start: ' . Carbon::now()->locale('en')->isoFormat('D MMMM HH:mm:ss'));
        $this->info('Fake init params:');
        $headers = ['QUANTITY_CONTAINERS', 'QUANTITY_ITEMS_IN_CONTAINER', 'QUANTITY_UNIQUE_NAMES',];
        $rows = [
            [
                $_ENV['QUANTITY_CONTAINERS'],
                $_ENV['QUANTITY_ITEMS_IN_CONTAINER'],
                $_ENV['QUANTITY_UNIQUE_NAMES'],
            ],
        ];
        $this->table($headers, $rows);

        // Создает контейнеры и имена товаров
        factory(Container::class, (int)$_ENV['QUANTITY_CONTAINERS'])->create();
        factory(Name::class, (int)$_ENV['QUANTITY_UNIQUE_NAMES'])->create();

        // Все получившиеся идентификаторы
        $allContainersId = Container::all()->pluck('id');

        // Создает товары одновременно формируя из них контейнеры
        // Количество товаров = количество контейнеров * количество товаров в контейнере
        for ($i = 1; $i <= $_ENV['QUANTITY_CONTAINERS']; $i++) {

            // Формирует список товаров в контейнере
            factory(\App\Models\Item::class, (integer)$_ENV['QUANTITY_ITEMS_IN_CONTAINER'])->create([
                'container_id' => $allContainersId[$i - 1],
            ]);
        }

        $this->info('Fake maker well done');
        $this->info('End: ' . Carbon::now()->locale('en')->isoFormat('D MMMM HH:mm:ss'));
        $this->info('//////////////////////////////////////');
    }
}
