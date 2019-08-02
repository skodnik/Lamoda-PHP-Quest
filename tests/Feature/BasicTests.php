<?php

namespace Tests\Feature;

use App\Models\Container;
use App\Models\Service;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BasicTests extends TestCase
{
    /*
     * Маршруты и коды ответов
     */

    public function testGetIndex()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function testGetHook()
    {
        $response = $this->get('/hook');
        $response->assertStatus(405);
    }

    public function testPostHook()
    {
        $this->withoutExceptionHandling();
        $response = $this->json('POST', '/hook');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success'           => false,
            'description'       => '',
            'error'             => true,
            'error_description' => 'Empty request',
        ]);
    }

    public function testGetId()
    {
        if ($containerId = Container::all()) {
            $containerId = 1;
        } else {
            $containerId = Container::all()->random()->id;
        }
        $response = $this->call('GET', '/container/' . $containerId);
        $response->assertStatus(200);
        $this->assertJson($response->getContent());
    }

    public function testGetId404()
    {
        $response = $this->call('GET', '/container/sometext');
        $response->assertStatus(404);
    }

    public function testGetUnique()
    {
        $response = $this->call('GET', '/containers-with-unique-items');
        $response->assertStatus(200);
        $this->assertJson($response->getContent());
    }

    /*
     * Проверка корректности
     */

    public function testCheckUnique()
    {
        $response = $this->call('GET', '/containers-with-unique-items');
        $containers = json_decode($response->getContent(), true);

        foreach ($containers as $container) {
            foreach ($container['items'] as $item) {
                $names[$item['name']][] = $container['id'];
            }
        }
        $this->assertCount($_ENV['QUANTITY_UNIQUE_NAMES'], $names);
    }

    /*
     * Исходные данные и запись в базу
     */

    public function testDataOneCheck()
    {
        $json = file_get_contents(__DIR__ . '/../../app/docs/samples/one_container.json');
        $this->assertJson($json);

        $data = json_decode($json, true);
        $inputCheckStructure = Service::inputCheckStructure($data);
        $this->assertEmpty($inputCheckStructure);
    }

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

    public function testContainerAndStuffStore()
    {
        $json = file_get_contents(__DIR__ . '/../../app/docs/samples/one_container.json');
        $data = json_decode($json, true);

        Container::where('id', $data['id'])->delete();

        $service = new Service();
        $service->data = $data;
        $report = $service->store();

        $this->assertIsArray($report->report);
        $this->assertDatabaseHas('containers', ['id' => $data['id']]);

        Container::where('id', $data['id'])->delete();
    }

    public function testContainersAndStuffStore()
    {
        $json = file_get_contents(__DIR__ . '/../../app/docs/samples/n_containers.json');
        $datas = json_decode($json, true);

        foreach ($datas as $data) {
            Container::where('id', $data['id'])->delete();
        }

        $service = new Service();
        $service->data = $datas;
        $report = $service->store();

        $this->assertIsArray($report->report);
        foreach ($datas as $data) {
            $this->assertDatabaseHas('containers', ['id' => $data['id']]);

            Container::where('id', $data['id'])->delete();
        }
    }
}
