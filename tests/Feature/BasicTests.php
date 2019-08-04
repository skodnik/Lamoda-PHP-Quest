<?php

namespace Tests\Feature;

use App\Models\Container;
use App\Models\Item;
use App\Models\Name;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicTests extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * Проверка метода контроля входных данных для одного контейнера
     */
    public function testDataOneCheck()
    {
        $json = file_get_contents(__DIR__ . '/../../app/docs/samples/one_container.json');
        $this->assertJson($json);

        $data = json_decode($json, true);
        $inputCheckStructure = Service::inputCheckStructure($data);
        $this->assertEmpty($inputCheckStructure);
    }

    /**
     * Проверка метода контроля входных данных для списка контейнеров
     */
    public function testDataNCheck()
    {
        $json = file_get_contents(__DIR__ . '/../../app/docs/samples/n_containers.json');
        $this->assertJson($json);

        $datas = json_decode($json, true);
        foreach ($datas as $data) {
            $inputCheckStructure = Service::inputCheckStructure($data);
            $this->assertEmpty($inputCheckStructure);
        }
    }

    /**
     * Проверка методов сохранения данных одного контейнера
     */
    public function testContainerAndStuffStore()
    {
        $json = file_get_contents(__DIR__ . '/../../app/docs/samples/one_container.json');
        $data = json_decode($json, true);

        $service = new Service();
        $service->data = $data;
        $report = $service->store();

        $this->assertIsArray($report->report);
        $this->assertDatabaseHas('containers', ['id' => $data['id']]);
        $this->assertDatabaseHas('names', ['name' => $data['items'][0]['name']]);
        $this->assertDatabaseHas('items', ['id' => $data['items'][0]['id']]);
    }

    /**
     * Проверка методов сохранения данных списка контейнеров
     */
    public function testContainersAndStuffStore()
    {
        $json = file_get_contents(__DIR__ . '/../../app/docs/samples/n_containers.json');
        $datas = json_decode($json, true);

        $service = new Service();
        $service->data = $datas;
        $report = $service->store();

        $this->assertIsArray($report->report);
    }

    /**
     * Проверка доступности сервиса
     */
    public function testGetIndex()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Проверка кода ответа на GET запрос по маршруту /hook
     */
    public function testGetHook()
    {
        $response = $this->get('/hook');
        $response->assertStatus(405);
    }

    /**
     * Проверка кода ответа на POST запрос и содержания в случае пустого запроса
     */
    public function testPostHook()
    {
        $this->withoutExceptionHandling();
        $response = $this->json('POST', '/hook');
        $response->assertStatus(200);
        $response->assertJsonFragment(['error_description' => 'Empty request']);
    }

    /**
     * Проверка неуспешной записи нового контейнера по причине некорректной структуры данных
     */
    public function testPostHookWithIncorrectData()
    {
        $datas = [
            ['id' => 1],
            ['id' => 1, 'name' => 'julia'],
            ['id' => 1, 'items' => 'some item'],
            ['name' => 'julia', 'items' => 'some item'],
            ['id' => 1, 'name' => 'julia', 'items' => 'some item'],
        ];

        foreach ($datas as $data) {
            $response = $this->json('POST', '/hook', $data);
            $response->assertJsonFragment(['success' => false]);
            $response->assertStatus(422);
        }
    }

    /**
     * Проверка корректности обработки POST запроса с передачей корректных данных
     * Проверка попытки записать дублирующиеся данные
     */
    public function testPostHookWithDataAndDuplicateData()
    {
        // Данные передаваемые в запросе
        $data = json_decode(file_get_contents(__DIR__ . '/../../app/docs/samples/one_container.json'), true);

        // Проверка успешной записи нового контейнера
        $response = $this->json('POST', '/hook', $data);
        $response->assertJsonFragment(['success' => true]);

        // Проверка попытки записи существующего контейнера
        $response = $this->json('POST', '/hook', $data);
        $response->assertJsonFragment(['error_description' => 'Probably duplicate entry']);
    }

    /**
     * Проверка ответа в случае некорректного запроса
     */
    public function testGetId404()
    {
        $response = $this->call('GET', '/container/sometext');
        $response->assertStatus(404);
    }

    /**
     * Проверка генератора случайных данных
     * Проверка получения одного случайного контейнера
     * Проверка получения списка контейнеров с уникальными товарами
     * Проверка корректности полученного списка соответствием количесту уникальных товаров
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
         * Проверка доступности маршрута для одгого контейнера
         * Получение одного контейнера по случайному идентификатору
         */
        $containerId = $containers->random()->id;

        $response = $this->call('GET', '/container/' . $containerId);
        $response->assertStatus(200);
        $this->assertJson($response->getContent());

        /**
         * Проверка доступности маршрута получения списка контейнеров с уникальными товарами
         * Проверка полученного результата путем сравнения полученного результата:
         * 1. подсчет уникальных товаров в выборке
         * 2. сравнение с заявленными параметрами
         */
        $response = $this->call('GET', '/containers-with-unique-items');
        $response->assertStatus(200);

        $containers = json_decode($response->getContent(), true);

        $names = [];
        foreach ($containers as $container) {
            foreach ($container['items'] as $item) {
                $names[$item['name']][] = $container['id'];
            }
        }
        $this->assertCount($_ENV['QUANTITY_UNIQUE_NAMES'], $names);
    }
}
