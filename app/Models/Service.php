<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    /**
     * Обработка сырого запроса
     *
     * @param bool $request
     *
     * @return $this
     */
    public function handle(): self
    {
        // Забирает входящие данные
        $request = file_get_contents('php://input');

        // Формирует массив из входящих данных
        $this->data = json_decode($request, true);

        // Формирует ответ в случае некорректности входящего формата данных
        if ($this->data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->report = [
                'success'           => false,
                'description'       => '',
                'error'             => true,
                'error_description' => 'JSON incorrect format',
            ];
        }

        return $this;
    }

    /**
     * Сохранение элементов
     *
     * @return Service
     */
    public function store()
    {
        // Если на предыдущем этапе возникла ошибка
        if ($this->report['error']) {
            return $this;
        }

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
                } catch (\Illuminate\Database\QueryException $e) {
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
            } catch (\Illuminate\Database\QueryException $e) {
                $this->report = [
                    'success'           => false,
                    'description'       => '',
                    'error'             => true,
                    'error_description' => 'Probably duplicate entry',
                ];
            }

        }

        return $this;
    }

    /**
     * Проверка корректности структуры входящего массива
     *
     * @param $data
     *
     * @return array|null
     */
    public static function inputCheckStructure($data)
    {
        $report = null;
        if (!isset($data['id']) || !isset($data['name']) || !isset($data['items'])) {
            $report = [
                'success'           => false,
                'description'       => '',
                'error'             => true,
                'error_description' => 'Incorrect input structure',
            ];
        }

        return $report;
    }
}
