<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Item;
use App\Models\Service;
use Illuminate\Http\Request;

class HookController extends Controller
{
    /**
     * Обработчик входящего запроса
     *
     * @param bool $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function main()
    {
        // Заглушка для пустого запроса, '&& !$request' используется для отладки
        if (!file_get_contents('php://input')) {
            return response()->json([
                'success'           => false,
                'description'       => '',
                'error'             => true,
                'error_description' => 'Empty request',
            ]);
        }

        // Инстанцирование объекта класса Service
        $service = new Service;

        // Основной цикл обработки запроса
        $service = $service
            ->handle()
            ->store();

        return response()->json($service->report);
    }

    /**
     * Обработчик запроса контейнера по уникальному идентификатору
     *
     * @param bool $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById($id)
    {
        // Находит искомый контейнер по идентификатору
        if (!$container = Container::where('id', $id)->first()) {
            return response()->json([
                'success'           => false,
                'description'       => '',
                'error'             => true,
                'error_description' => 'Container #' . $id . ' not found',
            ]);
        }

        // Забирает товары контейнера
        $items = $container->items;

        // Перебирает товары формируя массив для вывода
        foreach ($items as $item) {
            $i[] = ['id' => $item->id, 'name' => $item->name->name];
        }

        // Формирует массив вывода
        $toJson = ['id' => $container->id, 'name' => $container->name, 'items' => $i];

        // Отдает json
        return response()->json($toJson);
    }

    /**
     * Обработчик запроса на получение списка контейнеров содержащих уникальные товары
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnique()
    {
        // Выбирает контейнеры с уникальными товарами
        $containers = Service::getContainersWithUniqueItems();

        // Если не найдено ни одного контейнера
        if (!isset($containers)) {
            return response()->json([
                [
                    'success'           => false,
                    'description'       => '',
                    'error'             => true,
                    'error_description' => 'Containers not found',
                ],
            ]);
        }

        // Перебирает идентификаторы контейнеров
        foreach ($containers as $key => $id) {

            // Выбирает конкретный объект контейнера
            $container = Container::where('id', $id)->first();

            // Массив объектов товаров этого контейнера
            $items = $container->items;

            // Перебирает товары формируя массив для вывода
            foreach ($items as $item) {
                $itemsArray[$key][] = ['id' => $item->id, 'name' => $item->name->name];
            }

            // Формирует массив вывода
            $toJson[] = ['id' => $id, 'name' => $container->name, 'items' => $itemsArray[$key]];
        }

        // Отдает json
        return response()->json($toJson);
    }
}
