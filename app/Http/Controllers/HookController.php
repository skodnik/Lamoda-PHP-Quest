<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\JsonResponse;

class HookController extends Controller
{
    /**
     * Обработчик POST запроса на получение (сохранение) сервисом данных
     *
     * @return JsonResponse
     */
    public static function main(): JsonResponse
    {
        // Заглушка для пустого запроса
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

        // Сериализует и отдает ответ
        return response()->json($service->report);
    }

    /**
     * Обработчик GET запроса контейнера по уникальному идентификатору
     *
     * @param integer $id
     *
     * @return JsonResponse
     */
    public function getById($id): JsonResponse
    {
        // Инстанцирование объекта класса Service
        $service = new Service;

        // Получение контейнера по идентификатору, подготовка ответа
        $service = $service->getContainerById($id);

        // Отдает подготовленый ответ
        return $service->report;
    }

    /**
     * Обработчик GET запроса на получение списка контейнеров содержащих уникальные товары
     *
     * @return JsonResponse
     */
    public function getUnique(): JsonResponse
    {
        // Инстанцирование объекта класса Service
        $service = new Service;

        // Получение списка контейнеров, подготовка ответа
        $service = $service->getContainersWithUniqueItems();

        // Отдает подготовленый ответ
        return $service->report;
    }
}
