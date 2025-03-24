<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Commercial;
use Illuminate\Support\Facades\Log;
use App\Models\Deal;
use Illuminate\Support\Facades\Http;
class CommercialController extends Controller
{
    /**
     * CommercialController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Показать страницу с брифами.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function questions($id, $page)
{
    $titles = [
        1  => "Название зоны",
        2  => "Метраж зон",
        3  => "Зоны и их стиль оформления",
        4  => "Меблировка зон",
        5  => "Предпочтения отделочных материалов",
        6  => "Освещение зон",
        7  => "Кондиционирование зон",
        8  => "Напольное покрытие зон",
        9  => "Отделка стен зон",
        10 => "Отделка потолков зон",
        11 => "Категорически неприемлемо или нет",
        12 => "Бюджет на помещения",
        13 => "Пожелания и комментарии",
    ];
    $descriptions = [
        1  => "Укажите название каждой зоны (например, гостиная, кухня, спальня).",
        2  => "Укажите примерный размер каждой зоны в квадратных метрах.",
        3  => "Опишите стиль оформления для каждой зоны (например, минимализм, классика, лофт).",
        4  => "Укажите предпочитаемую мебель и её размещение в зонах.",
        5  => "Выберите материалы, которые хотите использовать для отделки зон.",
        6  => "Опишите тип освещения, который вы предпочитаете (например, точечное, люстры, настенные светильники).",
        7  => "Укажите, нужна ли система кондиционирования для зон.",
        8  => "Выберите предпочитаемый тип напольного покрытия (например, ламинат, паркет, плитка).",
        9  => "Опишите, как вы хотите оформить стены (например, обои, краска, панели).",
        10 => "Укажите пожелания по отделке потолков (например, натяжные, гипсокартон, покраска).",
        11 => "Перечислите материалы или решения, которые вы категорически не хотите использовать.",
        12 => "Укажите общий бюджет на проект.",
        13 => "Добавьте любые дополнительные пожелания или комментарии.",
    ];
    $title_site   = $titles[$page] ?? "Вопрос";
    $description  = $descriptions[$page] ?? "";
    $totalPages   = count($titles);

    // Ищем бриф по ID и текущему пользователю
    $brif = Commercial::where('id', $id)
                      ->where('user_id', auth()->id())
                      ->first();

    if (!$brif) {
        return redirect()->route('brifs.index')->with('error', 'Бриф не найден или не принадлежит вам.');
    }

    // Если бриф уже завершён
    if ($brif->status === 'Завершенный') {
        return redirect()->route('brifs.index')->with('info', 'Этот бриф уже завершён.');
    }

    $user         = Auth::user();
    $zones        = $brif->zones ? json_decode($brif->zones, true) : [];
    $preferences  = $brif->preferences ? json_decode($brif->preferences, true) : [];
    $budget       = $brif->price ?? 0;
    $zoneBudgets  = $brif->zone_budgets ? json_decode($brif->zone_budgets, true) : [];

    return view('commercial.questions', [
        'page'         => $page,
        'zones'        => $zones,
        'preferences'  => $preferences,
        'budget'       => $budget,
        'zoneBudgets'  => $zoneBudgets,
        'user'         => $user,
        'title_site'   => $title_site,
        'description'  => $description,
        'brif'         => $brif,
        'totalPages'   => $totalPages,
    ]);
}

   /**
 * Сохранение ответов для коммерческого брифа.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id       ID коммерческого брифа (commercial)
 * @param  int  $page     Номер страницы (шага)
 * @return \Illuminate\Http\RedirectResponse
 */
public function saveAnswers(Request $request, $id, $page)
{
    // 1. Находим бриф
    $commercial = Commercial::where('id', $id)
        ->where('user_id', auth()->id())
        ->first();

    // Если бриф не найден или не принадлежит пользователю
    if (!$commercial) {
        return redirect()
            ->route('brifs.index')
            ->with('error', 'Commercial brief not found or does not belong to you.');
    }

    // Если бриф уже завершен
    if ($commercial->status === 'Завершенный') {
        return redirect()
            ->route('brifs.index')
            ->with('info', 'This commercial brief is already completed.');
    }

    // 2. Обрабатываем данные в зависимости от страницы (шаг)
    switch ($page) {
        // -----------------
        // Шаг 1: создание списка зон (name, description)
        case 1:
            $data = $request->validate([
                'zones'            => 'required|array',
                'zones.*.name'     => 'required|string|max:255',
                'zones.*.description' => 'nullable|string|max:1000',
            ]);
            $commercial->zones = json_encode($data['zones']);
            break;

        // -----------------
        // Шаг 2: площади зон (total_area, projected_area)
        case 2:
            $data = $request->validate([
                'zones'                            => 'required|array',
                'zones.*.total_area'               => 'required|min:0',
                'zones.*.projected_area'           => 'required|min:0',
            ]);

            // Извлекаем массив зон из брифа
            $zones = json_decode($commercial->zones, true) ?? [];
            // Обновляем значения площадей
            foreach ($data['zones'] as $index => $zoneData) {
                if (isset($zones[$index])) {
                    $zones[$index]['total_area']     = $zoneData['total_area'];
                    $zones[$index]['projected_area'] = $zoneData['projected_area'];
                }
            }
            $commercial->zones = json_encode($zones);
            break;

        // -----------------
        // Шаг 13: Завершающий (по вашей логике). 
        // Здесь — установка общего бюджета, загрузка документов и завершение брифа.
        case 13:
            $data = $request->validate([
                'price'     => 'nullable|numeric|min:0',
                'budget'    => 'nullable|array',
                'documents' => 'nullable|array',
                'documents.*' => 'file|max:25600|mimes:pdf,xlsx,xls,doc,docx,jpg,jpeg,png,heic,heif',
            ]);

            // Сохраняем цену
            $commercial->price = $data['price'] ?? 0;

            // Сохраняем бюджеты по зонам
            if (isset($data['budget'])) {
                $zoneBudgets = [];
                foreach ($data['budget'] as $index => $budgetValue) {
                    // Убираем всё, кроме цифр
                    $cleanBudget = preg_replace('/\D/', '', $budgetValue);
                    if ($cleanBudget !== '') {
                        $zoneBudgets[$index] = floatval($cleanBudget);
                    }
                }
                $commercial->zone_budgets = json_encode($zoneBudgets);
            }

            // Загрузка файлов (документы)
            if ($request->hasFile('documents')) {
                $uploadedFiles = [];
                foreach ($request->file('documents') as $file) {
                    if ($file->isValid()) {
                        // Уникальное имя
                        $filename = uniqid() . '_' . $file->getClientOriginalName();

                        // Папка для загрузки
                        $userId   = auth()->id();
                        $briefId  = $commercial->id;
                        $directory = public_path("uploads/documents/user/{$userId}/commercials/{$briefId}");

                        // Создаем директорию, если не существует
                        if (!file_exists($directory)) {
                            mkdir($directory, 0755, true);
                        }

                        // Перемещаем файл
                        $file->move($directory, $filename);

                        // Сохраняем относительный путь
                        $uploadedFiles[] = "uploads/documents/user/{$userId}/commercials/{$briefId}/{$filename}";
                    }
                }

                // Массив уже имеющихся
                $existingDocuments = $commercial->documents
                    ? json_decode($commercial->documents, true)
                    : [];
                if (!is_array($existingDocuments)) {
                    $existingDocuments = [];
                }

                // Мержим старые и новые
                $commercial->documents = json_encode(
                    array_merge($existingDocuments, $uploadedFiles)
                );
            }

            // Завершаем бриф
            $commercial->status = 'Завершенный';
            $commercial->save();

            // Привязываем бриф к сделке
            $deal = Deal::where('user_id', auth()->id())->first();
            if ($deal) {
                $linkToBrief = "/commercial/{$commercial->id}";
                $deal->update([
                    'client_name'  => auth()->user()->name,
                    'client_phone' => auth()->user()->phone ?? 'N/A',
                    'total_sum'    => $commercial->price ?? 0,
                    'status'       => 'Бриф прикриплен',
                    'link'         => $linkToBrief,
                    'commercial_id'=> $commercial->id,
                ]);
                $commercial->deal_id = $deal->id;
                $commercial->save();

                // Пример уведомления координатору (если у сделки есть coordinator)
                $coordinator = $deal->coordinator ?? null;
                if ($coordinator && !empty($coordinator->phone)) {
                    $rawPhone    = $coordinator->phone;
                    $clientName  = auth()->user()->name ?? 'Client';
                    $viewLink    = url($linkToBrief);
                    $message     = "User {$clientName} has filled out the brief. View here: {$viewLink}";
                    
                    // Пример отправки SMS через sms.ru
                    $apiKey = '6CDCE0B0-6091-278C-5145-360657FF0F9B'; // Ваш ключ
                    Http::get("https://sms.ru/sms/send", [
                        'api_id' => $apiKey,
                        'to'     => $rawPhone,
                        'msg'    => $message,
                        'partner_id' => 1, // Не сокращать ссылку
                    ]);
                }
            }

            // Изменено: редирект на страницу сделки
            return redirect()
                ->route('deal.user')
                ->with('success', 'Бриф успешно заполнен!');
            // <-- Обратите внимание: здесь return, значит дальше код не пойдёт.

        // -----------------
        // Шаг 14 (если есть). Пример загрузки фотографий и т.д.
        case 14:
            // Пример если у вас есть 14-й шаг
            $data = $request->validate([
                'documents' => 'nullable|array',
                'documents.*' => 'file|max:25600|mimes:pdf,xlsx,xls,doc,docx,jpg,jpeg,png,heic,heif',
                'photos'    => 'nullable|array',
                'photos.*'  => 'file|max:10240|mimes:jpg,jpeg,png,heic,heif',
            ]);

            // Аналогично обрабатываем documents / photos
            // ...

            // Завершаем бриф
            $commercial->status = 'Завершенный';
            $commercial->save();

            return redirect()
                ->route('brifs.index')
                ->with('success', 'Бриф (шаг 14) успешно завершен!');
        // -----------------
        // По умолчанию (прочие страницы) — сохраняем "preferences"
        default:
            $data = $request->validate([
                'preferences' => 'nullable|array',
                'preferences.*.answer' => 'nullable|string|max:1000',
            ]);
            // Достаём из БД текущие preferences
            $preferences = json_decode($commercial->preferences, true) ?? [];
            foreach ($data['preferences'] ?? [] as $zoneIndex => $answers) {
                $preferences[$zoneIndex]['question_' . $page] = $answers['answer'] ?? null;
            }
            // Перезаписываем в бриф
            $commercial->preferences = json_encode($preferences);
            break;
    }

    // Сохраняем бриф
    $commercial->save();
    // Переходим к следующей странице
    return redirect()->route('commercial.questions', [
        'id'   => $commercial->id,
        'page' => $page + 1
    ]);
}
}