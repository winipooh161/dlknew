<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use App\Models\ObjectModel;
use App\Models\Estimate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
class SmetsController extends Controller
{
   // -----------------------------------------СМЕТЫ------------------------------------------------
    // добавляем стандартные значение в бд
    public function defaultValueBD()
    { // Путь к файлу CSV
        $csvFile = 'd2.csv';
        // Открытие файла CSV
        if (($handle = fopen($csvFile, 'r')) !== false) {
            // Чтение данных из файла CSV
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                // Создание нового объекта модели
                $object = new ObjectModel();
                // Заполнение свойств объекта данными из файла CSV
                $object->id = $data[0];
                $object->user_id = 16;
                $object->type = $data[2];
                $object->info = $data[3];
                $object->price = ($data[4] != 'null') ? str_replace(' ', '', $data[4]) : null;
                // $object->created_at = $data[5];
                // $object->updated_at = $data[6];
                $object->unit = ($data[7] != 'null') ? $data[7] : null;
                $object->stage = ($data[8] != 'null') ? $data[8] : null;
                // Сохранение объекта в базу данных
                $object->save();
            }
            fclose($handle);
        }
        echo 'Данные успешно добавлены в базу данных.';
    }
    // страница смет
    private function formatNumber($number)
    {
        $formattedNumber = number_format($number, 2, ',', '.');
        return $formattedNumber;
    }
    public function estimate()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->withErrors('You must be logged in to access estimates.');
        }
        $estimates = Estimate::where('user_id', $user->id)->get();
        $title_site = "Главная страница | Личный кабинет Экспресс-дизайн";
        return view('estimate', compact('estimates', 'user', 'title_site'));
    }
    // страница услуг
    public function allService()
    {
        // Проверка авторизации, берем id пользователя из users
        $user = auth()->user();
        $userId = $user->id;
        $admin_id = 16;
        // Выбираем все уникальные значения поля "info" из сервисов пользователя
        $userServicesInfo = ObjectModel::where('user_id', $userId)->pluck('info')->unique();
        // Выбираем все сервисы с такими значениями поля "info" из таблицы ObjectModel, исключая сервисы пользователя
        $filteredServices = ObjectModel::where('user_id', $admin_id)
            ->whereNotIn('info', $userServicesInfo)
            ->get();
        // Получаем все сервисы пользователя
        $userServices = ObjectModel::where('user_id', $userId)->get();
        // Объединяем $filteredServices с сервисами пользователя
        $filteredServices = $filteredServices->concat($userServices);
        // Сортируем сервисы по полю "info"
        $filteredServices = $filteredServices->sortBy(function ($service) {
            if ($service->position !== null) {
                return $service->position;
            } else {
                return $service->substage;
            }
        });
        $title_site = "Главная страница | Личный кабинет Экспресс-дизайн";
        // Возвращаем view: estimate.service и передаем вместе с объектами services, user
        return view('estimate.service', ['services' => $filteredServices, 'user' => $user],compact('title_site'));
    }
    public function addCoefs(Request $request)
    {
        // Получаем данные из инпутов
        $discount = $request->input('discount');
        $coefficient = $request->input('coefficient');
        $extraCharge = $request->input('extra_charge') ? $request->input('extra_charge') : null;
        // Удаляем все символы, кроме цифр и знака процента
        $discount = str_replace('%', '', $discount);
        $coefficient = preg_replace('/[^\d.%]/', '', $coefficient);
        // Собираем остальные текстовые поля в объект
        $about = $request->except(['discount', 'coefficient', 'extra_charge', '_token', 'id']);
        $about = preg_replace('/[^\d%]/', '', $about);
        $updated = Estimate::where('id', $request->input('id'))->update([
            'discount' => $discount,
            'coefficient' => $coefficient,
            'extra_charge' => $extraCharge,
            'about' => $about
        ]);
        if ($updated) {
            return redirect()->route('estimate.create');
        } else {
            return back()->withErrors(['error' => 'Ошибка при обновлении данных']);
        }
    }
    public function createEstimate($id = null)
    {
        $title_site = "Главная страница | Личный кабинет Экспресс-дизайн";
        $admin_id = 16;
        $user = auth()->user();
        $userId = $user->id;
        $userServicesInfo = ObjectModel::where('user_id', $userId)->pluck('info')->unique();
        // Выбираем все сервисы с такими значениями поля "info" из таблицы ObjectModel, исключая сервисы пользователя
        $filteredServices = ObjectModel::where('user_id', $admin_id)
            ->whereNotIn('info', $userServicesInfo)
            ->get();
        // Получаем все сервисы пользователя
        $userServices = ObjectModel::where('user_id', $userId)->get();
        // Объединяем $filteredServices с сервисами пользователя
        $filteredServices = $filteredServices->concat($userServices);
        // Сортируем сервисы по полю "info"
        $filteredServices = $filteredServices->sortBy(function ($service) {
            if ($service->position !== null) {
                return $service->position;
            } else {
                return $service->substage;
            }
        });
        // Поиск строки в таблице Estimates с указанными условиями
        if ($id) {
            $estimate = Estimate::where('id', $id)->first();
        } else {
            $estimate = Estimate::where('user_id', $userId)
                ->whereNull('info')
                ->first();
        }
        if ($id) {
            $jsonFilePath = public_path('json/' . $userId . '/estimate_' . $id . '.json');
            if (file_exists($jsonFilePath)) {
                $jsonData = file_get_contents($jsonFilePath);
                $data = json_decode($jsonData, true);
                $oldServises = $data['obj'];
            } else {
                $oldServises = null;
            }
        } else {
            $oldServises = null;
        }
        if ($estimate) {
            if ($estimate->coefficient && $estimate->discount) {
                // Если найдена строка с указанными условиями, возвращаем представление с этой строкой
                return view('create', [
                    'estimate' => $estimate,
                    'services' => $filteredServices,
                    'user' => $user,
                    'oldServises' => $oldServises,
                    'title_site' => $title_site 
                ]);
            } else {
                // Если найдена строка с указанными условиями, возвращаем представление с этой строкой
                return view('createCoefs', [
                    'estimate' => $estimate,
                    'services' => $filteredServices,
                    'user' => $user,
                    'title_site' => $title_site 
                ]);
            }
        } else {
            // Если такая строка не найдена, создаем новую запись в таблице Estimates
            $estimate = Estimate::create([
                'user_id' => $userId
            ]);
            // Перенаправляем пользователя на страницу создания сметы с передачей идентификатора сметы
            return view('createCoefs', [
                'estimate' => $estimate,
                'services' => $filteredServices,
                'user' => $user,
                'title_site' => $title_site 
            ]);
        }
    }
    public function saveEstimate(Request $request, $id)
    {
        $objectmodels = ObjectModel::all();
        $estimates = Estimate::all();
        $user = auth()->user();
        $userId = $user->id;
        if ($id) {
            $estimate = Estimate::where('id', $id)->first();
        } else {
            $estimate = Estimate::where('user_id', $userId)
                ->whereNull('info')
                ->first();
        }
        $requestData = $request->all();
        $obj = [];
        $price = 0; // добавляем переменную суммы
        $summEnd = 0;
        $priceDiscount = 0;
        $price = 0;
        $summNoDiscount = 0;
        foreach ($requestData as $key => $value) {
            if ($key != "_token") {
                if (strpos($key, 'stage-') === 0) {
                    $stageId = intval(substr($key, strlen('stage-')));
                    $stageInfo = [
                        'id' => $stageId,
                        'info' => [],
                    ];
                    $obj[str_replace('stage-', '', $value)] = $stageInfo;
                } elseif (strpos($key, 'counter-') === 0) {
                    $counterId = intval(substr($key, strlen('counter-')));
                    $element = ObjectModel::where('id', $counterId)->first();
                    $price = $element->price;
                    $discount = ($estimate->discount > 0.0) ? (1 - ($estimate->discount / 100)) : 1;
                    $priceDiscount = ($element->price * $discount) * $requestData[$key];
                    $counterInfo = [
                        'id' => $counterId,
                        'price' => $priceDiscount,
                        'count' => $requestData[$key],
                        'substage' => $element->substage,
                    ];
                    $substring = "info-";
                    $position = strpos($key, $substring);
                    $result = substr($key, $position + strlen($substring));
                    $lastKey = key(array_slice($obj, -1, 1, true));
                    $obj[$lastKey]['info'][$result] = $counterInfo;
                }
                $summEnd += $priceDiscount;
                $summNoDiscount += $price;
            }
        }
        // добавляем сумму в объект
        $summEnd = $this->formatNumber($summEnd);
        $summNoDiscount = $this->formatNumber($summNoDiscount);
        session()->put('obj', $obj);
        session()->put('summEnd', $summEnd);
        session()->put('summNoDiscount', $summNoDiscount);
        // Обновляем значение price в модели Estimate
        if ($estimate) {
            $estimate->price = $price;
            $estimate->save();
        }
        $title_site = "Главная страница | Личный кабинет Экспресс-дизайн";
        $isGeneratingPdf = false;
        // Возвращаем представление с массивом
        return view('estimate.preview', [
            'estimateData' => $obj,
            'user' => $user,
            'isGeneratingPdf' => $isGeneratingPdf,
            'estimate' => $estimate,
            'estimates' => $estimates,
            'objectmodels' => $objectmodels,
            'summEnd' => $summEnd,
            'summNoDiscount' => $summNoDiscount,
            'title_site' => $title_site
        ]);
    }
    public function savePdf(Request $request, $id = null)
    {
        $objectmodels = ObjectModel::all();
        $estimates = Estimate::all();
        $obj = session()->get('obj');
        $summEnd = session()->get('summEnd');
        $summNoDiscount = session()->get('summNoDiscount');
        $user = auth()->user();
        $userId = $user->id;
        if ($id) {
            $estimate = Estimate::where('id', $id)->first();
        } else {
            $estimate = Estimate::where('user_id', $userId)
                ->whereNull('info')
                ->first();
        }
        $data = [
            'obj' => $obj,
            'summEnd' => $summEnd,
            'estimate' => $estimate,
            'summNoDiscount' => $summNoDiscount,
            'user' => $user,
        ];
        $jsonFileName = 'estimate_' . $estimate->id . '.json';
        $jsonFileLocation = public_path('json/' . $userId . '/' . $jsonFileName);
        if (!is_dir(dirname($jsonFileLocation))) {
            mkdir(dirname($jsonFileLocation), 0755, true);
        }
        $jsonData = json_encode($data);
        $price = session()->get('totalPrice'); // Получаем значение totalPrice из сессии
        file_put_contents($jsonFileLocation, $jsonData);
        $estimate->save();
        if ($estimate) {
            // Создание экземпляра Dompdf
            $dompdf = new Dompdf();
            // Определение переменной $isGeneratingPdf
            $isGeneratingPdf = true;
            // Установка пути к шрифтам "DejaVu Sans"
            $dompdf->set_option('fontDir', public_path('fonts/'));
            $dompdf->set_option('defaultFont', 'DejaVu Sans');
            // Загрузка HTML-кода для создания PDF
            $html = view('estimate.preview', [
                'estimateData' => $obj,
                'user' => $user,
                'isGeneratingPdf' => $isGeneratingPdf,
                'estimate' => $estimate,
                'estimates' => $estimates,
                'objectmodels' => $objectmodels,
                'summEnd' => $summEnd,
                'summNoDiscount' => $summNoDiscount,
            ])->render();
            // Удаление кнопки "Создать смету" из HTML
            $html = str_replace('<button type="submit" class="smeta_estimate" id="createButton" onclick="removeButton()">Создать смету</button>', '', $html);
            // Установка HTML-кода в Dompdf
            $dompdf->loadHtml($html);
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->set_paper('A4', 'portrait');
            // Рендеринг PDF
            $dompdf->render();
            // Создание экземпляра класса Spreadsheet
            // Создание экземпляра класса Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Итого:' . $summEnd . '₽');
            $sheet->setCellValue('A2', 'Без скидки:' . $summNoDiscount . '₽');
            $sheet->setCellValue('A3', '№ Сметы:' . $estimate->id);
            $sheet->setCellValue('A4', 'Составил:' . $user->name);
            $sheet->setCellValue('A5', 'Телефон:' . $user->phone);
            $sheet->setCellValue('A6', 'Почта:' . $user->email);
            $sheet->mergeCells('A1:E1');
            $sheet->mergeCells('A2:E2');
            $sheet->mergeCells('A3:E3');
            $sheet->mergeCells('A4:E4');
            $sheet->mergeCells('A5:E5');
            $sheet->mergeCells('A6:E6');
            $sheet->setCellValue('A8', '№');
            $sheet->setCellValue('B8', 'Наименование');
            $sheet->setCellValue('C8', 'Единицы');
            $sheet->setCellValue('D8', 'Стоимость');
            $sheet->setCellValue('E8', 'Подэтап');
            $row = 9;
            $counterTabel = 1;
            foreach ($obj as $key => $data) {
                if ($key !== 'price') {
                    $sheet->setCellValue('A' . $row, $counterTabel);
                    $sheet->setCellValue('B' . $row, $key);
                    $sheet->setCellValue('C' . $row, '');
                    $sheet->setCellValue('D' . $row, '');
                    $sheet->setCellValue('E' . $row, '');
                    $sheet->mergeCells('B' . $row . ':E' . $row);
                    $row++;
                    foreach ($data['info'] as $subKey => $subData) {
                        $counterSubTabel = 1;
                        if (isset($subData['count']) && isset($subData['price'])) {
                            $sheet->setCellValue('A' . $row, $counterTabel . '.' . $counterSubTabel);
                            $sheet->setCellValue('B' . $row, str_replace('_', ' ', explode(',', $subKey)[0]));
                            $sheet->setCellValue('C' . $row, 'м2');
                            $sheet->setCellValue('D' . $row, $subData['price']);
                            $sheet->setCellValue('E' . $row, $subData['substage']);
                            $row++;
                        }
                    }
                }
                $counterTabel++;
            }
            $style = [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'name' => 'Times New Roman',
                ],
            ];
            $style2 = [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'name' => 'Times New Roman',
                ],
            ];
            $style3 = [
                'font' => [
                    'bold' => false,
                    'size' => 14,
                    'name' => 'Times New Roman',
                ],
            ];
            $lastRow = $sheet->getHighestRow();
            $sheet->getStyle('A1:E6')->applyFromArray($style);
            $sheet->getStyle('A8:E8')->applyFromArray($style2);
            $sheet->getStyle('A9:E' . $lastRow)->applyFromArray($style3);
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            // Сохранение Excel файла
            $excelFileName = 'estimate_' . $estimate->id . '.xlsx';
            $excelFileLocation = public_path('excel/' . $userId . '/' . $excelFileName);
            if (!is_dir(dirname($excelFileLocation))) {
                mkdir(dirname($excelFileLocation), 0755, true);
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save($excelFileLocation);
            // Сохранение PDF файла
            $pdfFileName = 'estimate_' . $estimate->id . '.pdf';
            $pdfFileLocation = public_path('pdf/' . $userId . '/' . $pdfFileName);
            if (!is_dir(dirname($pdfFileLocation))) {
                mkdir(dirname($pdfFileLocation), 0755, true);
            }
            file_put_contents($pdfFileLocation, $dompdf->output());
            // Обновление информации о файле PDF и Excel в базе данных
            $estimate->info = $pdfFileName;
            $estimate->excel_info = $excelFileName;
            $estimate->save();
            // Сохранение файлов PDF и Excel прошло успешно
            return redirect()->route('estimate');
        } else {
            // Ошибка при получении данных о смете
            return redirect()->back()->with('error', 'Ошибка при получении данных о смете');
        }
    }
    public function delEstimate($id)
    {
        $estimates = Estimate::all();
        $user = auth()->user();
        $userId = $user->id;
        // Найти смету по id
        $estimate = Estimate::find($id);
        if ($estimate) {
            // Получить имя файла PDF
            $fileName = 'estimate_' . $id . '.pdf';
            // Удалить запись из базы данных
            $estimate->delete();
            // Удалить файл PDF
            $filePath = public_path('pdf/' . $userId . '/' . $fileName);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $filePath = public_path('json/' . $userId . '/' . 'estimate_' . $id . '.json');
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            // Возвращаем успешный ответ или выполняем другие действия
            return response()->json(['message' => 'Смета успешно удалена']);
        } else {
            // Возвращаем ошибку, если смета с указанным id не найдена
            return response()->json(['error' => 'Смета не найдена'], 404);
        }
    }
    public function changeService($id, $slot, $value, $type, $stage, Request $request)
    {
        if ($value == 'none') {
            $value = $request->input('unitValue') ? $request->input('unitValue') : null;
        }
        $idParent = $request->input('idParent') ? $request->input('idParent') : null;
        $idParent = $request->input('idParent') ? $request->input('idParent') : null;
        if ($stage == 'null') {
            $stage = null;
        }
        $user = auth()->user();
        $userId = $user->id;
        $adminId = 16;
        $object = ObjectModel::find($id);
        $parent = ObjectModel::find($idParent);
        if ($object && $object->user_id !== $userId) {
            $newObject = $object->replicate();
            $newObject->$slot = $value;
            $newObject->user_id = $userId;
            $newObject->save();
            return response()->json(['success' => true, 'id' => $newObject->id]);
        } elseif ($object) {
            $object->$slot = $value;
            $object->stage = $parent ? $parent->info : null;
            $object->save();
            return response()->json(['success' => true, 'id' => $object->id]);
        } elseif ($type) {
            // Проверяем, существует ли объект с таким значением поля "info" для текущего пользователя
            $existingObject = ObjectModel::where('info', $value)->where('user_id', $userId)->first();
            if ($existingObject) {
                $existingObject->$slot = $value;
                $existingObject->stage = $parent ? $parent->info : null;
                $existingObject->save();
                return response()->json(['success' => true, 'id' => $existingObject->id]);
            } else {
                $newObject = new ObjectModel;
                $newObject->$slot = $value;
                $newObject->type = $type;
                $newObject->user_id = $userId;
                $newObject->stage = $parent ? $parent->info : null;
                $newObject->save();
                return response()->json(['success' => true, 'id' => $newObject->id]);
            }
        }
        // Другие действия, если необходимо
        return response()->json(['success' => false, 'id' => null]);
    }
    public function defaultServices()
    {
        $user = auth()->user();
        $userId = $user->id;
        $adminId = 16;
        if ($userId !== $adminId) {
            ObjectModel::where('user_id', $userId)->delete();
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }
    public function copyEstimate(Request $request, $id)
    {
        if ($id) {
            $estimate = Estimate::where('id', $id)->first();
            $user_id = $estimate->user_id;
            $jsonFilePath = public_path('json/' . $user_id . '/estimate_' . $id . '.json');
            if (file_exists($jsonFilePath)) {
                $jsonData = file_get_contents($jsonFilePath);
                $data = json_decode($jsonData, true);
                $NewEstimate = Estimate::create([
                    'user_id' => $user_id,
                    'discount' => $data['estimate']['discount'],
                    'coefficient' => $data['estimate']['coefficient'],
                    'extra_charge' => $data['estimate']['extra_charge'],
                    'about' => $data['estimate']['about'],
                    'price' => $estimate->price,
                ]);
                $new_id = $NewEstimate->id;
                $newJsonFilePath = public_path('json/' . $user_id . '/estimate_' . $NewEstimate->id . '.json');
                if (file_exists($jsonFilePath)) {
                    copy($jsonFilePath, $newJsonFilePath);
                }
                $obj = $data['obj'];
                $summEnd = $data['summEnd'];
                $summNoDiscount = $data['summNoDiscount'];
                session()->put('obj', $obj);
                session()->put('summEnd', $summEnd);
                session()->put('summNoDiscount', $summNoDiscount);
                $objectmodels = ObjectModel::all();
                $estimates = Estimate::all();
                $user = auth()->user();
                $isGeneratingPdf = true;
                $requestData = new Request([
                    'estimateData' => $obj,
                    'user' => $user,
                    'isGeneratingPdf' => $isGeneratingPdf,
                    'estimate' => $estimate,
                    'estimates' => $estimates,
                    'objectmodels' => $objectmodels,
                    'summEnd' => $summEnd,
                    'summNoDiscount' => $summNoDiscount,
                ]);
                // Вызов функции создания PDF
                return $this->savePdf($requestData, $new_id);
            }
        } else {
            // Invalid ID
            return redirect()->back()->with('error', 'Invalid ID');
        }
    }
    public function changeEstimate()
    {
        return redirect()->route('estimate');
    }
}