<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Chat;
use App\Models\User;
use App\Models\DealChangeLog;
use App\Models\DealFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DealsController extends Controller
{
    public function __construct()
    {
        // При необходимости добавьте middleware для аутентификации
    }

    /**
     * Отображение списка сделок.
     * В выборку включаются только те сделки, к которым привязан текущий пользователь.
     */
    public function dealCardinator(Request $request)
    {
        $title_site = "Сделки | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();

        $search = $request->input('search');
        $status = $request->input('status');
        $view_type = $request->input('view_type', 'blocks');
        $viewType = $view_type;

        $query = Deal::query();

        if ($user->status === 'admin') {
            // без фильтра
        } elseif ($user->status === 'partner') {
            $query->where('office_partner_id', $user->id);
        } elseif ($user->status === 'coordinator') {
            $query->where('coordinator_id', $user->id);
        } else {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('client_phone', 'LIKE', "%{$search}%")
                  ->orWhere('client_email', 'LIKE', "%{$search}%")
                  ->orWhere('project_number', 'LIKE', "%{$search}%")
                  ->orWhere('package', 'LIKE', "%{$search}%")
                  ->orWhere('deal_note', 'LIKE', "%{$search}%")
                  ->orWhere('client_city', 'LIKE', "%{$search}%")
                  ->orWhere('total_sum', 'LIKE', "%{$search}%");
            });
        }

        if ($status && $status !== 'null') {
            $query->where('status', $status);
        }

        $deals = $query->get();

        $deal = null;

        $statuses = [
            'Ждем ТЗ', 'Планировка', 'Коллажи', 'Визуализация', 'Рабочка/сбор ИП',
            'Проект готов', 'Проект завершен', 'Проект на паузе', 'Возврат',
            'В работе', 'Завершенный', 'На потом', 'Регистрация',
            'Бриф прикриплен', 'Поддержка', 'Активный'
        ];

        $feeds = DealFeed::whereIn('deal_id', $deals->pluck('id'))->get();

        return view('cardinators', compact(
            'deals',
            'title_site',
            'search',
            'status',
            'viewType',
            'deal',
            'statuses',
            'feeds'
        ));
    }

 /**
     * Метод для загрузки ленты комментариев по сделке.
     * Вызывается AJAX‑запросом и возвращает JSON с записями ленты.
     */
    public function getDealFeeds($dealId)
    {
        try {
            $deal = Deal::findOrFail($dealId);
            $feeds = $deal->dealFeeds()->with('user')->orderBy('created_at', 'desc')->get();
            $result = $feeds->map(function ($feed) {
                return [
                    'user_name'  => $feed->user->name,
                    'content'    => $feed->content,
                    'date'       => $feed->created_at->format('d.m.Y H:i'),
                    'avatar_url' => $feed->user->avatar_url ? $feed->user->avatar_url : asset('storage/default-avatar.png'),
                ];
            });
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error("Ошибка загрузки ленты: " . $e->getMessage());
            return response()->json(['error' => 'Ошибка загрузки ленты'], 500);
        }
    }

    /**
     * Отображение информации о сделках для клиента.
     */
    public function dealUser()
    {
        $user = Auth::user();

        if ($user->status === 'partner') {
            return redirect()->route('deal.cardinator');
        }

        $title_site = "Информация о сделке";
        $userDeals = Deal::with('coordinator', 'users', 'briefs')
            ->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();

        foreach ($userDeals as $deal) {
            $groupChat = Chat::where('deal_id', $deal->id)
                ->where('type', 'group')
                ->first();

            if (!$groupChat) {
                $responsibleIds = $deal->users->pluck('id')->toArray();
                if (!in_array($deal->user_id, $responsibleIds)) {
                    $responsibleIds[] = $deal->user_id;
                }
                $groupChat = Chat::create([
                    'name'    => "Групповой чат сделки: {$deal->name}",
                    'type'    => 'group',
                    'deal_id' => $deal->id,
                ]);
                $groupChat->users()->attach($responsibleIds);
            }
            $deal->setAttribute('groupChat', $groupChat);
        }

        return view('user', compact('title_site', 'user', 'userDeals'));
    }

    /**
     * Форма создания сделки – доступна для координатора, администратора и партнёра.
     */
    public function createDeal()
    {
        $user = Auth::user();
        if (!in_array($user->status, ['coordinator', 'admin', 'partner'])) {
            return redirect()->route('deal.cardinator')
                ->with('error', 'Только координатор, администратор или партнер могут создавать сделку.');
        }
        $title_site = "Создание сделки";

        $citiesFile = public_path('cities.json');
        if (file_exists($citiesFile)) {
            $citiesJson = file_get_contents($citiesFile);
            $russianCities = json_decode($citiesJson, true);
        } else {
            $russianCities = [];
        }

        $coordinators = User::where('status', 'coordinator')->get();
        $partners = User::where('status', 'partner')->get();

        return view('create_deal', compact(
            'title_site',
            'user',
            'coordinators',
            'partners',
            'russianCities'
        ));
    }

    /**
     * Сохранение сделки с автоматическим созданием группового чата для ответственных.
     */
    public function storeDeal(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->status, ['coordinator', 'admin', 'partner'])) {
            return redirect()->route('deal.cardinator')
                ->with('error', 'Только координатор, администратор или партнер могут создавать сделку.');
        }

        try {
            $validated = $request->validate([
                'name'                    => 'required|string|max:255',
                'client_phone'            => 'required|string|max:50',
                'package'                 => 'required|string|max:255',
                'project_number'          => 'nullable|string|max:21',
                'price_service_option'    => 'required|string|max:255',
                // Изменено: добавлено ограничение max для поля rooms_count_pricing
                'rooms_count_pricing'     => 'nullable|integer|min:1|max:2147483647',
                'execution_order_comment' => 'nullable|string|max:1000',
                'execution_order_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'office_partner_id'       => 'nullable|exists:users,id',
                'coordinator_id'          => 'nullable|exists:users,id',
                'total_sum'               => 'nullable|numeric',
                'measuring_cost'          => 'nullable|numeric',
                'client_info'             => 'nullable|string',
                'payment_date'            => 'nullable|date',
                'execution_comment'       => 'nullable|string',
                'comment'                 => 'nullable|string',
                'client_timezone'         => 'nullable|string',
                'completion_responsible'  => 'nullable|string',
                'start_date'              => 'nullable|date',
                'project_duration'        => 'nullable|integer',
                'project_end_date'        => 'nullable|date',
            ]);

            $coordinatorId = $validated['coordinator_id'] ?? auth()->id();

            $deal = Deal::create([
                'name'                   => $validated['name'],
                'client_phone'           => $validated['client_phone'],
                'status'                 => 'Ждем ТЗ', // устанавливаем значение по умолчанию
                'package'                => $validated['package'],
                'client_name'            => $validated['name'],
                'project_number'         => $validated['project_number'] ?? null,
                'price_service'          => $validated['price_service_option'],
                'rooms_count_pricing'    => $validated['rooms_count_pricing'] ?? null,
                'execution_order_comment'=> $validated['execution_order_comment'] ?? null,
                'office_partner_id'      => $validated['office_partner_id'] ?? null,
                'coordinator_id'         => $coordinatorId,
                'total_sum'              => $validated['total_sum'] ?? null,
                'measuring_cost'         => $validated['measuring_cost'] ?? null,
                'client_info'            => $validated['client_info'] ?? null,
                'payment_date'           => $validated['payment_date'] ?? null,
                'execution_comment'      => $validated['execution_comment'] ?? null,
                'comment'                => $validated['comment'] ?? null,
                'client_timezone'        => $validated['client_timezone'] ?? null,
                'completion_responsible' => $validated['completion_responsible'] ?? null,
                'user_id'                => auth()->id(),
                'registration_token'     => Str::random(32),
                'registration_token_expiry' => now()->addDays(7),
                'start_date'             => $validated['start_date'] ?? null,
                'project_duration'       => $validated['project_duration'] ?? null,
                'project_end_date'       => $validated['project_end_date'] ?? null,
            ]);

            // Загрузка файлов
            $fileFields = [
                'avatar',
                'execution_order_file',
            ];

            foreach ($fileFields as $field) {
                $uploadData = $this->handleFileUpload($request, $deal, $field, $field === 'avatar' ? 'avatar_path' : $field);
                if (!empty($uploadData)) {
                    $deal->update($uploadData);
                }
            }

            // Привязываем текущего пользователя как координатора
            $deal->users()->attach([auth()->id() => ['role' => 'coordinator']]);

            // Создаем групповой чат для сделки
            $this->createGroupChatForDeal($deal, [auth()->id()]);

            // Отправляем смс с регистрационной ссылкой
            $this->sendSmsNotification($deal, $deal->registration_token);

            return redirect()->route('deal.cardinator')->with('success', 'Сделка успешно создана.');
        } catch (\Exception $e) {
            Log::error("Ошибка при создании сделки: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при создании сделки: ' . $e->getMessage());
        }
    }

    /**
     * Создание группового чата для сделки.
     */
    private function createGroupChatForDeal($deal, $userIds)
    {
        $chat = Chat::create([
            'name'    => "Групповой чат сделки: {$deal->name}",
            'type'    => 'group',
            'deal_id' => $deal->id,
            'slug'    => (string) \Illuminate\Support\Str::uuid(), // Добавляем значение для slug
        ]);

        $validUserIds = User::whereIn('id', $userIds)->pluck('id')->toArray();
        if (!in_array($deal->user_id, $validUserIds)) {
            $validUserIds[] = $deal->user_id;
        }
        $chat->users()->attach($validUserIds);
    }

    /**
     * Логирование изменений сделки.
     */
    protected function logDealChanges($deal, $original, $new)
    {
        foreach (['updated_at', 'created_at'] as $key) {
            unset($original[$key], $new[$key]);
        }

        $changes = [];
        foreach ($new as $key => $newValue) {
            if (array_key_exists($key, $original) && $original[$key] != $newValue) {
                $changes[$key] = [
                    'old' => $original[$key],
                    'new' => $newValue,
                ];
            }
        }

        if (!empty($changes)) {
            DealChangeLog::create([
                'deal_id'   => $deal->id,
                'user_id'   => Auth::id(),
                'user_name' => Auth::user()->name,
                'changes'   => $changes,
            ]);
        }
    }

    /**
     * Отправка SMS-уведомления с регистрационной ссылкой.
     */
    private function sendSmsNotification($deal, $registrationToken)
    {
        if (!$registrationToken) {
            Log::error("Отсутствует регистрационный токен для сделки ID: {$deal->id}");
            throw new \Exception('Отсутствует регистрационный токен для сделки.');
        }

        $rawPhone = preg_replace('/\D/', '', $deal->client_phone);
        $registrationLinkUrl = route('register_by_deal', ['token' => $registrationToken]);
        $apiKey = '6CDCE0B0-6091-278C-5145-360657FF0F9B';

        $response = Http::get("https://sms.ru/sms/send", [
            'api_id'    => $apiKey,
            'to'        => $rawPhone,
            'msg'       => "Здравствуйте! Для регистрации пройдите по ссылке: $registrationLinkUrl",
            'partner_id'=> 1,
        ]);

        if ($response->failed()) {
            Log::error("Ошибка при отправке SMS для сделки ID: {$deal->id}. Ответ сервера: " . $response->body());
            throw new \Exception('Ошибка при отправке SMS.');
        }
    }

    /**
     * Обработка загрузки файлов.
     */
    private function handleFileUpload(Request $request, $deal, $field, $targetField = null)
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            // изменено: обрабатываем и "avatar", и "avatar_path" как аватар сделки
            if ($field === 'avatar' || $field === 'avatar_path') {
                $dir = "dels/{$deal->id}"; // изменено: файл сохраняется в папку dels/{id сделки}
                $fileName = "avatar." . $request->file($field)->getClientOriginalExtension(); // имя файла всегда "avatar"
            } else {
                $dir = "dels/{$deal->id}";
                $fileName = $field . '.' . $request->file($field)->getClientOriginalExtension();
            }
            $filePath = $request->file($field)->storeAs($dir, $fileName, 'public');
            return [$targetField ?? $field => $filePath]; // для аватара "avatar_path" будет установлен путь сохранённого файла
        }
        return [];
    }

    /**
     * Обновление сделки с учетом ролей пользователя.
     */
   /**
     * Обновление сделки с учетом ролей пользователя.
     */
    public function updateDeal(Request $request, $id)
    {
        try {
            $deal = Deal::with(['coordinator', 'responsibles'])->findOrFail($id);
            $original = $deal->getOriginal();
            $user = Auth::user();

            $baseRules = [
                'name'          => 'nullable|string|max:255',
                'client_phone'  => 'nullable|string',
                'client_info'   => 'nullable|string',
                'client_email'  => 'nullable|email',
                'comment'       => 'nullable|string',
                'deal_note'     => 'nullable|string',
                'avatar_path'   => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
                'created_date'  => 'nullable|date',
            ];

            if (in_array($user->status, ['coordinator', 'admin'])) {
                $baseRules = array_merge($baseRules, [
                    'status'                     => 'nullable|string',
                    'package'                    => 'nullable|string|max:255',
                    'project_number'             => 'nullable|string|max:21',
                    'price_service_option'       => 'nullable|string|max:255',
                    'rooms_count_pricing'        => 'nullable|integer|min:1',
                    'execution_order_comment'    => 'nullable|string|max:1000',
                    'execution_order_file'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                    'office_partner_id'          => 'nullable|exists:users,id',
                    'coordinator_id'             => 'nullable|exists:users,id',
                    'measuring_cost'             => 'nullable|numeric',
                    'payment_date'               => 'nullable|date',
                    'execution_comment'          => 'nullable|string',
                    'office_equipment'           => 'nullable|boolean',
                    'measurement_comments'       => 'nullable|string|max:1000',
                    'measurements_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png,dwg|max:5120',
                    'start_date'                 => 'nullable|date',
                    'project_duration'           => 'nullable|integer',
                    'project_end_date'           => 'nullable|date',
                    'architect_id'               => 'nullable|exists:users,id',
                    'final_floorplan'            => 'nullable|file|mimes:pdf|max:20480',
                    'designer_id'                => 'nullable|exists:users,id',
                    'final_collage'              => 'nullable|file|mimes:pdf|max:204800',
                    'visualizer_id'              => 'nullable|exists:users,id',
                    'visualization_link'         => 'nullable|url',
                    'final_project_file'         => 'nullable|file|mimes:pdf|max:204800',
                    'work_act'                   => 'nullable|file|mimes:pdf|max:10240',
                    'client_project_rating'      => 'nullable|numeric',
                    'architect_rating_client'    => 'nullable|numeric',
                    'architect_rating_partner'   => 'nullable|numeric',
                    'architect_rating_coordinator'=> 'nullable|numeric',
                    'designer_rating_client'     => 'nullable|numeric',
                    'designer_rating_partner'    => 'nullable|numeric',
                    'designer_rating_coordinator'=> 'nullable|numeric',
                    'visualizer_rating_client'   => 'nullable|numeric',
                    'visualizer_rating_partner'  => 'nullable|numeric',
                    'visualizer_rating_coordinator'=> 'nullable|numeric',
                    'coordinator_rating_client'  => 'nullable|numeric',
                    'coordinator_rating_partner' => 'nullable|numeric',
                    'coordinator_comment'        => 'nullable|string',
                    'chat_screenshot'            => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                    'archicad_file'              => 'nullable|file|mimes:pln,dwg|max:307200',
                    'contract_number'            => 'nullable|string|max:100',
                    'contract_attachment'        => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
                    'responsibles'               => 'nullable|array',
                    'responsibles.*'             => 'exists:users,id',
                    'completion_responsible'     => 'nullable|string',
                    'client_city'                => 'nullable|string',
                ]);
            } else {
                $baseRules = array_merge($baseRules, [
                    'total_sum' => 'nullable|numeric',
                ]);
            }

            $validated = $request->validate($baseRules);
            $updateData = $validated;

            $deal->update($updateData);
            $this->logDealChanges($deal, $original, $deal->getAttributes());

            $fileFields = ($user->status === 'partner')
                ? ['avatar_path']
                : [
                    'avatar_path',
                    'execution_order_file',
                    'measurements_file',
                    'final_floorplan',
                    'final_collage',
                    'final_project_file',
                    'work_act',
                    'chat_screenshot',
                    'archicad_file',
                    'contract_attachment',
                ];

            foreach ($fileFields as $field) {
                $uploadData = $this->handleFileUpload($request, $deal, $field, $field);
                if (!empty($uploadData)) {
                    $deal->update($uploadData);
                }
            }

            if ($request->has('responsibles') && in_array($user->status, ['coordinator', 'admin'])) {
                $responsibles = collect($request->input('responsibles'))->map(function($id) {
                    return ['role' => 'responsible'];
                })->toArray();

                $validResponsibles = User::whereIn('id', array_keys($responsibles))->pluck('id')->toArray();
                $responsibles = array_intersect_key($responsibles, array_flip($validResponsibles));
                $responsibles[Auth::id()] = ['role' => 'coordinator'];

                $deal->users()->sync($responsibles);
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'deal' => $deal]);
            }

            return redirect()->route('deal.cardinator')->with('success', 'Сделка успешно обновлена.');
        } catch (\Exception $e) {
            Log::error("Ошибка при обновлении сделки: " . $e->getMessage(), ['exception' => $e]);
            if ($request->ajax()) {
                return response()->json(['error' => 'Ошибка при обновлении сделки.'], 500);
            }
            return redirect()->back()->with('error', 'Ошибка при обновлении сделки.');
        }
    }

    public function storeDealFeed(Request $request, $dealId)
    {
        $request->validate([
            'content' => 'required|string|max:1990',
        ]);

        $deal = Deal::findOrFail($dealId);
        $user = Auth::user();

        $feed = new DealFeed();
        $feed->deal_id = $deal->id;
        $feed->user_id = $user->id;
        $feed->content = $request->input('content');
        $feed->save();

        return response()->json([
            'user_name'  => $user->name,
            'content'    => $feed->content,
            'date'       => $feed->created_at->format('d.m.Y H:i'),
            'avatar_url' => $user->avatar_url,
        ]);
    }

    /**
     * Отображение логов изменений для конкретной сделки.
     */
    public function changeLogsForDeal($dealId)
    {
        $deal = Deal::findOrFail($dealId);
        $logs = DealChangeLog::where('deal_id', $deal->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $title_site = "Логи изменений сделки";
        return view('deal_change_logs', compact('deal', 'logs', 'title_site'));
    }
}
