<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Chat;
use App\Models\DealFeed;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DealModalController extends Controller
{
    /**
     * Отображение модального окна для сделки.
     */
  

    // Добавим алиас для метода getDealModal, который используется в маршрутах
    public function getDealModal($id)
    {
        try {
            $deal = Deal::with(['coordinator', 'responsibles', 'users'])->findOrFail($id);
            $feeds = DealFeed::where('deal_id', $id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            $groupChat = Chat::where('deal_id', $id)
                ->where('type', 'group')
                ->first();

            // Если групповой чат не найден – создаём его
            if (!$groupChat) {
                $responsibleIds = $deal->users->pluck('id')->toArray();
                if (!in_array($deal->user_id, $responsibleIds)) {
                    $responsibleIds[] = $deal->user_id;
                }
                $groupChat = Chat::create([
                    'name'    => "Групповой чат сделки: {$deal->name}",
                    'type'    => 'group',
                    'deal_id' => $deal->id,
                    'slug'    => (string) Str::uuid(),
                ]);
                $groupChat->users()->attach($responsibleIds);
            }

            // Формирование полей сделки (пример для модуля "Заказ")
            $dealFields = $this->getDealFields();

            return response()->json([
                'html' => view('deals.partials.dealModal', compact('deal', 'feeds', 'groupChat', 'dealFields'))->render()
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка отображения модального окна сделки: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Ошибка при загрузке данных сделки: ' . $e->getMessage()], 500);
        }
    }

    private function getDealFields() {
        return [
            'zakaz' => [
                [
                    'name' => 'project_number',
                    'label' => '№ проекта',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'maxlength' => 21,
                ],
                [
                    'name' => 'avatar_path',
                    'label' => 'Аватар сделки',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'image/*',
                ],
                [
                    'name' => 'package',
                    'label' => 'Пакет',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                    ],
                ],
                [
                    'name' => 'status',
                    'label' => 'Статус',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [
                        'Ждем ТЗ' => 'Ждем ТЗ',
                        'Планировка' => 'Планировка',
                        'Коллажи' => 'Коллажи',
                        'Визуализация' => 'Визуализация',
                        'Рабочка/сбор ИП' => 'Рабочка/сбор ИП',
                        'Проект готов' => 'Проект готов',
                        'Проект завершен' => 'Проект завершен',
                        'Проект на паузе' => 'Проект на паузе',
                        'Возврат' => 'Возврат',
                        'Регистрация' => 'Регистрация',
                        'Бриф прикриплен' => 'Бриф прикриплен',
                    ],
                ],
                [
                    'name' => 'price_service_option',
                    'label' => 'Услуга по прайсу',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin', 'partner'],
                    'options' => [
                        'экспресс планировка' => 'Экспресс планировка',
                        'экспресс планировка с коллажами' => 'Экспресс планировка с коллажами',
                        'экспресс проект с электрикой' => 'Экспресс проект с электрикой',
                        'экспресс планировка с электрикой и коллажами' => 'Экспресс планировка с электрикой и коллажами',
                        'экспресс проект с электрикой и визуализацией' => 'Экспресс проект с электрикой и визуализацией',
                        'экспресс рабочий проект' => 'Экспресс рабочий проект',
                        'экспресс эскизный проект с рабочей документацией' => 'Экспресс эскизный проект с рабочей документацией',
                        'экспресс 3Dвизуализация' => 'Экспресс 3Dвизуализация',
                        'экспресс полный дизайн-проект' => 'Экспресс полный дизайн-проект',
                        '360 градусов' => '360 градусов',
                    ],
                    'required' => true,
                ],
                [
                    'name' => 'rooms_count_pricing',
                    'label' => 'Кол-во комнат по прайсу',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'execution_order_comment',
                    'label' => 'Комментарий к заказу',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                    'maxlength' => 1000,
                ],
                [
                    'name' => 'coordinator_id',
                    'label' => 'Координатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'coordinator')->pluck('name', 'id')->toArray(),
                ],
                [
                    'name' => 'name',
                    'label' => 'ФИО клиента',
                    'type' => 'text',
                    'id'   => 'nameField',
                    'role' => ['coordinator', 'admin'],
                    'required' => true,
                ],
                [
                    'name' => 'client_phone',
                    'label' => 'Телефон',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                    'required' => true,
                ],
                [
                    'name' => 'client_city',
                    'label' => 'Город/Часовой пояс',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [], // Заполняется через AJAX
                ],
                [
                    'name' => 'client_email',
                    'label' => 'Email клиента',
                    'type' => 'email',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'office_partner_id',
                    'label' => 'Партнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'partner')->pluck('name', 'id')->toArray(),
                ],
                [
                    'name' => 'completion_responsible',
                    'label' => 'Кто делает комплектацию',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => [
                        'клиент' => 'Клиент',
                        'партнер' => 'Партнер',
                        'шопинг-лист' => 'Шопинг-лист',
                        'закупки и снабжение от УК' => 'Нужны закупки и снабжение от УК',
                    ],
                ],
                [
                    'name' => 'contract_number',
                    'label' => '№ договора',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'created_date',
                    'label' => 'Дата создания сделки',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'payment_date',
                    'label' => 'Дата оплаты',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'total_sum',
                    'label' => 'Сумма заказа',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.01',
                ],
                [
                    'name' => 'contract_attachment',
                    'label' => 'Приложение',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf,image/jpeg,image/jpg',
                ],
                [
                    'name' => 'deal_note',
                    'label' => 'Примечание',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'measurements_file',
                    'label' => 'Замеры',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => '.pdf,.dwg,image/*',
                ],
                [
                    'name' => 'measurements_comment',
                    'label' => 'Комментарии по замерам',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                ],
            ],
            'rabota' => [
                [
                    'name' => 'start_date',
                    'label' => 'Дата старта работы по проекту',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                ],
                [
                    'name' => 'project_duration',
                    'label' => 'Общий срок проекта (в рабочих днях)',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin', 'partner'],
                ],
                [
                    'name' => 'project_end_date',
                    'label' => 'Дата завершения',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin', 'partner'],
                ],
                 [
                    'name' => 'architect_id',
                    'label' => 'Архитектор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'architect')->pluck('name', 'id')->toArray(),
                ],
                [
                    'name' => 'plan_final',
                    'label' => 'Планировка финал (PDF, до 20мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                ],
                [
                    'name' => 'designer_id',
                    'label' => 'Дизайнер',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'designer')->pluck('name', 'id')->toArray(),
                ],
                [
                    'name' => 'final_collage',
                    'label' => 'Коллаж финал (PDF, до 200мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                ],
                [
                    'name' => 'visualizer_id',
                    'label' => 'Визуализатор',
                    'type' => 'select',
                    'role' => ['coordinator', 'admin'],
                    'options' => User::where('status', 'visualizer')->pluck('name', 'id')->toArray(),
                ],
                [
                    'name' => 'visualization_link',
                    'label' => 'Ссылка на визуализацию',
                    'type' => 'url',
                    'role' => ['coordinator', 'admin', 'partner'],
                ],
                [
                    'name' => 'final_project_file',
                    'label' => 'Финал проекта (PDF, до 200мб)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                ],
            ],
            'final' => [
                [
                    'name' => 'work_act',
                    'label' => 'Акт выполненных работ (PDF)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf',
                ],
                [
                    'name' => 'client_project_rating',
                    'label' => 'Оценка за проект (от клиента)',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.5',
                ],
                [
                    'name' => 'architect_rating_client',
                    'label' => 'Оценка архитектора (Клиент)',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.5',
                ],
                [
                    'name' => 'architect_rating_partner',
                    'label' => 'Оценка архитектора (Партнер)',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.5',
                ],
                [
                    'name' => 'architect_rating_coordinator',
                    'label' => 'Оценка архитектора (Координатор)',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.5',
                ],
                [
                    'name' => 'chat_screenshot',
                    'label' => 'Скрин чата с оценкой и актом (JPEG)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'image/jpeg,image/jpg,image/png',
                ],
                [
                    'name' => 'coordinator_comment',
                    'label' => 'Комментарий координатора',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                    'maxlength' => 1000,
                ],
                [
                    'name' => 'archicad_file',
                    'label' => 'Исходный файл архикад (pln, dwg)',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => '.pln,.dwg',
                ],
                [
                    'name' => 'contract_number',
                    'label' => '№ договора',
                    'type' => 'text',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'created_date',
                    'label' => 'Дата создания сделки',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'payment_date',
                    'label' => 'Дата оплаты',
                    'type' => 'date',
                    'role' => ['coordinator', 'admin'],
                ],
                [
                    'name' => 'total_sum',
                    'label' => 'Сумма Заказа',
                    'type' => 'number',
                    'role' => ['coordinator', 'admin'],
                    'step' => '0.01',
                ],
                [
                    'name' => 'contract_attachment',
                    'label' => 'Приложение договора',
                    'type' => 'file',
                    'role' => ['coordinator', 'admin'],
                    'accept' => 'application/pdf,image/jpeg,image/jpg,image/png',
                ],
                [
                    'name' => 'deal_note',
                    'label' => 'Примечание',
                    'type' => 'textarea',
                    'role' => ['coordinator', 'admin'],
                ],
            ],
        ];
    }
}