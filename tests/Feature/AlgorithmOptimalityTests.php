<?php

namespace Tests\Feature;

use App\Models\Container;
use App\Models\Item;
use App\Models\Name;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlgorithmOptimalityTests extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * Проверка генератора случайных данных
     * Проверка получения списка контейнеров с уникальными товарами
     * Проверка корректности полученного списка соответствием количесту уникальных товаров
     * Проверка оптимальности алгоритма путем сравнения с минимальным количеством в выборке
     */
    public function testMakeFakeGetIdGetUniqueCheckUnique()
    {
        /**
         * Генератор случайных данных
         */
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

        // Получение сгенерированных данных из базы
        $containers = Container::all();
        $names = Name::all();
        $items = Item::all();

        // Проверка соответсвия заданных параметров полученному результату
        $this->assertCount($_ENV['QUANTITY_CONTAINERS'], $containers);
        $this->assertCount($_ENV['QUANTITY_UNIQUE_NAMES'], $names);
        $this->assertCount($_ENV['QUANTITY_CONTAINERS'] * $_ENV['QUANTITY_ITEMS_IN_CONTAINER'], $items);

        /**
         * Проверка доступности маршрута получения списка контейнеров с уникальными товарами
         * Проверка полученного результата путем сравнения полученного результата:
         * 1. подсчет уникальных товаров в выборке
         * 2. сравнение с заявленными параметрами
         */
        $response = $this->call('GET', '/containers-with-unique-items');
        $response->assertStatus(200);

        // Контейнеры из результата
        $containers = json_decode($response->getContent(), true);

        $names = [];
        foreach ($containers as $container) {
            foreach ($container['items'] as $item) {
                $names[$item['name']][] = $container['id'];
            }
        }
        $this->assertCount($_ENV['QUANTITY_UNIQUE_NAMES'], $names);

        $containersFromHook = count($containers);

        /**
         * Тестирование оптимальности алгоритма
         * Сравнивается количество контейнеров полученное в результате работы метода с минимальным
         */

        // Все контейнеры из базы
        $containers = Container::with('items')->get();

        // Подготовка массивов
        $result = [];
        $names = [];

        // В каждом контейнере из списка товаров формирует массив с идентификатоами имен товаров
        foreach ($containers as $container) {
            foreach ($container->items->toArray() as $item) {
                $names[$item['name_id']][] = $container->id;
            }
        }

        // Сортировка массива групп имен по количеству элементов по возрастанию
        // Используется для определения максимального индекса массива
        array_multisort(array_map('count', $names), SORT_ASC, $names);

        // Максимальный индекс равен минимальному числу элементов в массиве групп имен
        $max = count($names[0]);

        // Перебор групп имен с выборкой перечня контейнеров по номеру ключа
        foreach ($names as $name) {
            for ($i = 0; $i < $max; $i++) {
                $result[$i][] = $name[$i];
                $containersFromGroup[$i] = count(array_unique($result[$i]));
            }
        }

        // Непосредственное сравнение значений
        $this->assertEquals($containersFromHook, min($containersFromGroup));

        // Для наглядности дамп сравниваемых значений
        echo PHP_EOL . 'Containers from the result: ' . $containersFromHook . ' estimated minimum: ' . min($containersFromGroup);
    }
}
