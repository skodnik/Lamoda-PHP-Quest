<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

/**
 * @property mixed report
 * @property array containers
 * @property mixed data
 * @property Request request
 */
class Service extends Model
{
    /**
     * Сохранение элементов
     *
     * @return Service
     */
    public function store(): self
    {
        $data = $this->data;

        // Если это список контейнерв
        if (isset($data[0])) {

            // Определяет массив контейнеров
            $containers = $data;

            // Перебирает контейнеры
            foreach ($containers as $key => $container) {

                // Проверка корректности структуры
                if ($report = self::inputCheckStructure($data[$key])) {
                    $this->report = $report;
                    return $this;
                }

                try {
                    // Инстанциует новый контейнер
                    $containerNew = new Container();
                    $containerNew->id = $data[$key]['id'];
                    $containerNew->name = $data[$key]['name'];
                    $containerNew->save();

                    // Массив товаров в этом контейнере
                    $items = $data[$key]['items'];

                    // Перебирает товары
                    foreach ($items as $keyI => $item) {

                        // Инстанцирует товар
                        $itemNew = new Item();
                        $itemNew->id = $items[$keyI]['id'];
                        $itemNew->container_id = $data[$key]['id'];

                        // Если имя товара уже существует, берет его объект, если нет, инстанцирует новый товар
                        $nameNew = Name::firstOrNew(['name' => $items[$keyI]['name']]);
                        $nameNew->save();

                        $itemNew->name_id = $nameNew->id;
                        $itemNew->save();
                    }
                    $this->report = [
                        'success'           => true,
                        'description'       => count($items) . ' containers successfully stored',
                        'error'             => false,
                        'error_description' => '',
                    ];
                } catch (QueryException $e) {
                    $this->report = [
                        'success'           => false,
                        'description'       => '',
                        'error'             => true,
                        'error_description' => 'Probably duplicate some entry or entries',
                    ];
                }

            }

            // Если это один контейнер
        } else {

            // Проверка корректности структуры
            if ($report = self::inputCheckStructure($data)) {
                $this->report = $report;
                return $this;
            }

            try {
                // Инстанциует новый контейнер
                $containerNew = new Container();
                $containerNew->id = $data['id'];
                $containerNew->name = $data['name'];
                $containerNew->save();

                // Массив товаров в этом контейнере
                $items = $data['items'];

                // Перебирает товары
                foreach ($items as $key => $item) {

                    // Инстанцирует товар
                    $itemNew = new Item();
                    $itemNew->id = $items[$key]['id'];
                    $itemNew->container_id = $data['id'];

                    // Если имя товара уже существует, берет его объект, если нет, инстанцирует новый товар
                    $nameNew = Name::firstOrNew(['name' => $items[$key]['name']]);
                    $nameNew->save();

                    $itemNew->name_id = $nameNew->id;
                    $itemNew->save();
                }

                $this->report = [
                    'success'           => true,
                    'description'       => 'Container #' . $data['id'] . ' successfully stored',
                    'error'             => false,
                    'error_description' => '',
                ];
            } catch (QueryException $e) {
                $this->report = [
                    'success'           => false,
                    'description'       => '',
                    'error'             => true,
                    'error_description' => 'Probably duplicate entry',
                ];
            }

        }

        // Возвращает готовый объект
        return $this;
    }

    /**
     * Проверка корректности структуры входящего массива
     *
     * @param $data
     *
     * @return array|null
     */
    public static function inputCheckStructure($data): ?array
    {
        $report = null;

        // Проверка существования ключей входящего массива при необходимости расширить проверкой ключей массива товаров
        if (!isset($data['id'], $data['name'], $data['items'])) {
            $report = [
                'success'           => false,
                'description'       => '',
                'error'             => true,
                'error_description' => 'Incorrect input structure',
            ];
        }

        // Возвращает массив ответа или null
        return $report;
    }

    /**
     * Получает контейнер по его идентификатору
     *
     * @param $id
     *
     * @return $this
     */
    public function getContainerById($id)
    {
        // Находит искомый контейнер по идентификатору
        if (!$container = Container::where('id', $id)->first()) {
            $this->report = response()->json([
                'success'           => false,
                'description'       => '',
                'error'             => true,
                'error_description' => 'Container #' . $id . ' not found',
            ]);

            return $this;
        }

        // Забирает товары контейнера
        $items = $container->items;

        // Подготовка массива
        $i = [];

        // Перебирает товары формируя массив для вывода
        foreach ($items as $item) {
            $i[] = ['id' => $item->id, 'name' => $item->name->name];
        }

        // Формирует массив вывода
        $toJson = ['id' => $container->id, 'name' => $container->name, 'items' => $i];

        // Формирует свойства объекта
        $this->report = response()->json($toJson);

        // Возвращает готовый объект
        return $this;
    }

    /**
     * Формирует массив идентификаторов контейнеров содержащих уникальные товары
     *
     * @return Service
     */
    public function getContainersWithUniqueItems(): self
    {
        // Забирает все контейнеры
        $containers = Container::with('items')->get();

        // Если контейнеры не найдены
        if ($containers->count() === 0) {
            $this->report = response()->json([
                [
                    'success'           => false,
                    'description'       => '',
                    'error'             => true,
                    'error_description' => 'Containers not found',
                ],
            ]);
            return $this;
        }

        // Подготовка массивов
        $result = [];
        $toJson = [];
        $names = [];

        // В каждом контейнере из списка товаров формирует массив с идентификатоами имен товаров
        foreach ($containers as $container) {
            foreach ($container->items->toArray() as $item) {
                $names[$item['name_id']][] = $container->id;
            }
        }

        // Выбирает контейнеры из первых элементов
        // Тут можно добавить условия оценки результирующих массивов при изменении индекса для оптимизации результата
        foreach ($names as $name) {
            $result[] = $name[0];
        }

        // Убирает повторяющиеся идентификаторы
        $containers = array_unique($result);

        // Перебирает идентификаторы контейнеров
        foreach ($containers as $key => $id) {

            // Выбирает конкретный объект контейнера
            $container = Container::where('id', $id)->first();

            // Массив объектов товаров этого контейнера
            $items = $container->items;

            // Подготовка массива
            $itemsArray = [];

            // Перебирает товары формируя массив для вывода
            foreach ($items as $item) {
                $itemsArray[$key][] = ['id' => $item->id, 'name' => $item->name->name];
            }

            // Формирует массив вывода
            $toJson[] = ['id' => $id, 'name' => $container->name, 'items' => $itemsArray[$key]];
        }

        // Формирует свойства объекта
        $this->containers = $containers;
        $this->report = response()->json($toJson);

        // Возвращает готовый объект
        return $this;
    }
}
