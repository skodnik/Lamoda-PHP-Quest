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
        $headers = ['QUANTITY_CONTAINERS', 'QUANTITY_ITEMS_IN_CONTAINER', 'QUANTITY_UNIQUE_NAMES', 'QUANTITY_NAMES'];
        $rows = [
            [
                $_ENV['QUANTITY_CONTAINERS'],
                $_ENV['QUANTITY_ITEMS_IN_CONTAINER'],
                $_ENV['QUANTITY_UNIQUE_NAMES'],
                $_ENV['QUANTITY_NAMES'],
            ],
        ];
        $this->table($headers, $rows);

        // Формирование массивов уникальных и повторяющихся имен
        $namesAll = range(1, $_ENV['QUANTITY_NAMES']);
        shuffle($namesAll);
        $namesUnique = array_slice($namesAll, 0, $_ENV['QUANTITY_UNIQUE_NAMES']);
        $namesRepeating = array_diff($namesAll, $namesUnique);

        // Формирование массива имен равного итоговому количеству товаров во всех контейнерах
        $totalNamesCount = $_ENV['QUANTITY_CONTAINERS'] * $_ENV['QUANTITY_ITEMS_IN_CONTAINER'];

        $totalNames = $namesUnique;
        while (count($totalNames) < $totalNamesCount) {
            $totalNames = array_merge($totalNames, $namesRepeating);
        }
        shuffle($totalNames);

        // Создает контейнеры и имена товаров
        factory(Container::class, (int)$_ENV['QUANTITY_CONTAINERS'])->create();
        factory(Name::class, (int)$_ENV['QUANTITY_NAMES'])->create();

        // Создает товары одновременно формируя из них контейнеры
        // Количество товаров = количество контейнеров * количество товаров в контейнере
        for ($i = 1; $i <= $_ENV['QUANTITY_CONTAINERS']; $i++) {

            // Получает идентификатор контейнера по порядку
            $nextContainerId = Container::all()->get($i - 1)->id;

            // Формирует список товаров в контейнере
            factory(\App\Models\Item::class, (integer)$_ENV['QUANTITY_ITEMS_IN_CONTAINER'])->create([
                'container_id' => $nextContainerId,
            ]);
        }

        $this->info('Fake maker well done');
        $this->info('End: ' . Carbon::now()->locale('en')->isoFormat('D MMMM HH:mm:ss'));
        $this->info('//////////////////////////////////////');
    }
}
