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
        // Забирает все товары с уникальными именами (не unique т.к., тогда нет количества инстансов)
        $itemsOneCopy = Item::all()->groupBy(['name_id']);

        // Перебирает коллекцию и если у товара только один инстанс, то берет идентификатор контейнера из него
        // Формирует массив номеров контейнеров
        foreach ($itemsOneCopy as $item) {
            if ($item->count() === 1) {
                $containersId[] = $item[0]->container_id;
            }
        }

        // Если не найдено ни одного уникального товара
        if (!isset($containersId)) {
            return response()->json([
                [
                    'success'           => false,
                    'description'       => '',
                    'error'             => true,
                    'error_description' => 'Unique items not found',
                ],
            ]);
        }

        // Убирает дубликаты из массива номеров контейнеров
        $containersIdOneCopy = array_unique($containersId);

        // Перебирает идентификаторы контейнеров
        foreach ($containersIdOneCopy as $key => $id) {

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
