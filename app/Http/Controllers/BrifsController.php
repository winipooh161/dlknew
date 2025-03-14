<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Common;
use App\Models\Deal;
use App\Models\Commercial;
class BrifsController extends Controller
{
    /**
     * BrifsController constructor.
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
    public function index()
    {
        $title_site = "Ваши брифы | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();
    
        // Заголовки страниц для типов брифов
        $pageTitlesCommon = [
            'Люди, питомцы', 'Общая информация', 'Зональность', 'Балкон', 'Комплектация', 'Освещение',
            'Кухонная зона', 'Теплый пол', 'Кондиционирование', 'Ванная комната', 'Напольные покрытия',
            'Освещение', 'Потолки', 'Бюджет', 'Заключение',
        ];
    
        $pageTitlesCommercial = [
            'Зоны и их функционал', 'Метраж зон', 'Зоны и их ', 'Мебилировка зон',
            'Отделочные материалы', 'Освещение зон', 'Кондиционирование',
            'Напольное покрытие зон', 'Отделка стен зон', 'Отделка потолков зон',
            'Категорически неприемлемо', 'Бюджет на помещения', 'Пожелания',
        ];
    
        // Получаем брифы пользователя
        $activeCommon = Common::where('user_id', auth()->id())->where('status', 'Активный')->get();
        $inactiveCommon = Common::where('user_id', auth()->id())->where('status', 'Завершенный')->get();
        $activeCommercial = Commercial::where('user_id', auth()->id())->where('status', 'Активный')->get();
        $inactiveCommercial = Commercial::where('user_id', auth()->id())->where('status', 'Завершенный')->get();
    
        // Объединяем активные брифы в один массив и сортируем по дате создания (новые сверху)
        $activeBrifs = $activeCommon->merge($activeCommercial)->sortByDesc('created_at');
    
        // Объединяем неактивные брифы и сортируем аналогично
        $inactiveBrifs = $inactiveCommon->merge($inactiveCommercial)->sortByDesc('created_at');
    
        return view('brifs', compact(
            'activeBrifs', 'inactiveBrifs', 'pageTitlesCommercial', 'pageTitlesCommon', 'user', 'title_site'
        ));
    }
    
    /**
     * Отображение формы создания брифов.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function common_create()
    {$user = Auth::user();
        $title_site = "Создать Общий бриф | Личный кабинет Экспресс-дизайн";
        return view('common.create',compact('title_site', 'user'));
    }
    public function common_store(Request $request)
    {
        $brif = Common::create([
            'title' => 'Общий бриф',
            'description' => 'Общий бриф представляет собой структурированный опрос, направленный на выявление базовых предпочтений и потребностей клиента. Он включает вопросы о личных предпочтениях, функциональных особенностях помещения, количестве жильцов, а также об основных стилевых решениях. Заполнение этого брифа позволяет нам лучше понять ваш образ жизни и создать проект, который гармонично объединит практичность, уют и эстетическое единство интерьера.',
            'status' => 'Активный',
            'article' => Str::random(15), // Генерация случайной строки
            'user_id' => auth()->id(), // Привязка к текущему пользователю
        ]);
        return redirect()->route('common.questions', [
                   'id'   => $brif->id,
                   'page' => 1
               ]);
    }
    public function commercial_create()
    { $user = Auth::user();
        $title_site = "Создать Коммерческий бриф | Личный кабинет Экспресс-дизайн";
        return view('commercial.create',compact('title_site', 'user'));
    }
    /**
     * Отображение конкретного брифа.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function common_show($id)
    {
        $title_site = "Детали брифа | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();
        $pageTitlesCommon = [
            'Люди, питомцы',
            'Общая информация',
            'Зональность',
            'Балкон',
            'Предпочтения по комплектации',
            'Пожелания по освещение',
            'Кухонная зона',
            'Теплый пол',
            'Кондиционирование',
            'Ванная комната',
            'Напольные покрытия',
            'Освещение',
            'Отделка потолка',
            'Бюджет',
            'Заключение',
        ];
        $questions = [
            1 => [
                ['key' => 'question_1_1', 'title' => 'Какое количество членов семьи собирается проживать в квартире или доме?', 'subtitle' => 'Опишите всех членов семьи с их возрастом', 'type' => 'textarea', 'placeholder' => 'Пример: Варвара 24г, Дочь 23г', 'format' => 'default'],
                ['key' => 'question_1_2', 'title' => 'Какое количество домашних животных и комнатных растений находится в наличии?', 'subtitle' => '(вероятность пополнения в ближайшем будущем)', 'type' => 'textarea', 'placeholder' => 'Пример: Кактус 2шт, Барсик кот', 'format' => 'default'],
            ],
            2 => [
                ['key' => 'question_2_1', 'title' => 'Есть ли у вас мебель? Укажите ее размеры:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, Кровать - 160*190см, Диван: длина – 150-170 см, ширина – 60-70 см', 'format' => 'default'],
                ['key' => 'question_2_2', 'title' => 'Нужен ли проём/арка в несущей стене?', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Нужен ли проём/арка в несущей стене?', 'format' => 'default'],
                ['key' => 'question_2_3', 'title' => 'Необходимость звукоизоляции', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например: да, я хочу сделать звукоизоляцию.', 'format' => 'default'],
                ['key' => 'question_2_4', 'title' => 'Требуется ли перепланировка?', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какую комнату вы хотите увеличить/уменьшить и для каких целей.', 'format' => 'default'],
                ['key' => 'question_2_5', 'title' => 'Наличие хобби, предполагающие размещение дополнительных инструментов', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например: да, я занимаюсь спортом дома. Мне нужно место под хранение спортивного снаряжения.', 'format' => 'default'],
                ['key' => 'question_2_6', 'title' => 'Как часто к вам приходят гости?', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какое количество гостей вы принимаете у себя дома. Нужно ли расширить пространство для вашего общения.', 'format' => 'default'],
            ],
            3 => [
                ['key' => 'question_3_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, разместить зеркало на двери, вешалку для одежды у двери.', 'format' => 'faq'],
                ['key' => 'question_3_2', 'title' => 'Детская', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу разделить пространство в детской на игровую зону и зону отдыха.', 'format' => 'faq'],
                ['key' => 'question_3_3', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, организовать пространство под хранение одежды. Установить гладильную доску и зеркало.', 'format' => 'faq'],
                ['key' => 'question_3_4', 'title' => 'Кухня и гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, на кухне вы готовите и принимаете пищу, а гостиная для особых случаем – приёма гостей. ', 'format' => 'faq'],
                ['key' => 'question_3_5', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу перегородку в совмещенном санузле.', 'format' => 'faq'],
                ['key' => 'question_3_6', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какое количество гостей вы можете принять одновременно. Как предпочитаете проводить время.', 'format' => 'faq'],
                ['key' => 'question_3_7', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу организовать рабочую зону на балконе.', 'format' => 'faq'],
                ['key' => 'question_3_8', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, совмещена ли столовая с кухней/гостиной. Как часто принимаете пищу в этой комнате?', 'format' => 'faq'],
                ['key' => 'question_3_9', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу выделить зону душа глянцевой светлой плиткой, чтобы не было видно разводов. ', 'format' => 'faq'],
                ['key' => 'question_3_10', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу кухню с барной стойкой.', 'format' => 'faq'],
                ['key' => 'question_3_11', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в кабинете разделить пространство на рабочую зону и зону для занятия спортом. ', 'format' => 'faq'],
                ['key' => 'question_3_12', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в спальне выделить уголок под домашнюю библиотеку.', 'format' => 'faq'],
                ['key' => 'question_3_13', 'title' => 'Гардеробная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, площадь комнаты.', 'format' => 'faq'],
                ['key' => 'question_3_14', 'title' => 'Другое', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'faq'],
            ],
            4 => [
                ['key' => 'question_4_1', 'title' => 'Остекленный полностью', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_4_2', 'title' => 'Открытый', 'subtitle' => '', 'type' => 'checkbox',   'format' => 'checkpoint'],
                ['key' => 'question_4_3', 'title' => 'Устройство зимнего сада', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_4_4', 'title' => 'Какой стиль Вы хотите видеть в своём интерьере? Какие цвета должны преобладать в интерьере?', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, мне нравится стиль лофт. Хочу, чтобы в интерьере преобладали белые, черные и коричневые цвета.', 'format' => 'default'],
                ['key' => 'question_4_5', 'title' => 'Хочу видеть в своем будущем интерьере:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, больше деревянной мебели.', 'format' => 'default'],
                ['key' => 'question_4_6', 'title' => 'Категорически не хочу видеть в своём будущем интерьере:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, пластиковые стулья.', 'format' => 'default'],
            ],
            5 => [
                ['key' => 'question_5_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, разместить зеркало на двери, вешалку для одежды у двери.', 'format' => 'faq'],
                ['key' => 'question_5_2', 'title' => 'Детская', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу разделить пространство в детской на игровую зону и зону отдыха.', 'format' => 'faq'],
                ['key' => 'question_5_3', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, организовать пространство под хранение одежды. Установить гладильную доску и зеркало.', 'format' => 'faq'],
                ['key' => 'question_5_4', 'title' => 'Кухня и гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, на кухне вы готовите и принимаете пищу, а гостиная для особых случаем – приёма гостей. ', 'format' => 'faq'],
                ['key' => 'question_5_5', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу перегородку в совмещенном санузле.', 'format' => 'faq'],
                ['key' => 'question_5_6', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какое количество гостей вы можете принять одновременно. Как предпочитаете проводить время.', 'format' => 'faq'],
                ['key' => 'question_5_7', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу организовать рабочую зону на балконе.', 'format' => 'faq'],
                ['key' => 'question_5_8', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, совмещена ли столовая с кухней/гостиной. Как часто принимаете пищу в этой комнате?', 'format' => 'faq'],
                ['key' => 'question_5_9', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу выделить зону душа глянцевой светлой плиткой, чтобы не было видно разводов. ', 'format' => 'faq'],
                ['key' => 'question_5_10', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу кухню с барной стойкой.', 'format' => 'faq'],
                ['key' => 'question_5_11', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в кабинете разделить пространство на рабочую зону и зону для занятия спортом. ', 'format' => 'faq'],
                ['key' => 'question_5_12', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в спальне выделить уголок под домашнюю библиотеку.', 'format' => 'faq'],
                ['key' => 'question_5_13', 'title' => 'Гардеробная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, площадь комнаты.', 'format' => 'faq'],
                ['key' => 'question_5_14', 'title' => 'Другое', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'faq'],
            ],
            6 => [
                ['key' => 'question_6_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, разместить зеркало на двери, вешалку для одежды у двери.', 'format' => 'faq'],
                ['key' => 'question_6_2', 'title' => 'Детская', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу разделить пространство в детской на игровую зону и зону отдыха.', 'format' => 'faq'],
                ['key' => 'question_6_3', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, организовать пространство под хранение одежды. Установить гладильную доску и зеркало.', 'format' => 'faq'],
                ['key' => 'question_6_4', 'title' => 'Кухня и гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, на кухне вы готовите и принимаете пищу, а гостиная для особых случаем – приёма гостей. ', 'format' => 'faq'],
                ['key' => 'question_6_5', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу перегородку в совмещенном санузле.', 'format' => 'faq'],
                ['key' => 'question_6_6', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какое количество гостей вы можете принять одновременно. Как предпочитаете проводить время.', 'format' => 'faq'],
                ['key' => 'question_6_7', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу организовать рабочую зону на балконе.', 'format' => 'faq'],
                ['key' => 'question_6_8', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, совмещена ли столовая с кухней/гостиной. Как часто принимаете пищу в этой комнате?', 'format' => 'faq'],
                ['key' => 'question_6_9', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу выделить зону душа глянцевой светлой плиткой, чтобы не было видно разводов. ', 'format' => 'faq'],
                ['key' => 'question_6_10', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу кухню с барной стойкой.', 'format' => 'faq'],
                ['key' => 'question_6_11', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в кабинете разделить пространство на рабочую зону и зону для занятия спортом. ', 'format' => 'faq'],
                ['key' => 'question_6_12', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в спальне выделить уголок под домашнюю библиотеку.', 'format' => 'faq'],
                ['key' => 'question_6_13', 'title' => 'Гардеробная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, площадь комнаты.', 'format' => 'faq'],
                ['key' => 'question_6_14', 'title' => 'Другое', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'faq'],
            ],
            7 => [
                ['key' => 'question_7_1', 'title' => 'Водонагреватель', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_2', 'title' => 'Фильтр для воды', 'subtitle' => '', 'type' => 'checkbox',   'format' => 'checkpoint'],
                ['key' => 'question_7_3', 'title' => 'Мультиварка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_4', 'title' => 'Холодильник', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_5', 'title' => 'Подсветка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_6', 'title' => 'Мойка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_7', 'title' => 'Защита от протечек', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_8', 'title' => 'Посудомойка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_9', 'title' => 'Мини-бар', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_10', 'title' => 'Духовой шкаф', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_11', 'title' => 'Измельчитель  отходов', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_12', 'title' => 'Пароварка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_13', 'title' => 'Микроволновка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_14', 'title' => 'Вытяжка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_7_15', 'title' => 'Плита:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, на сколько конфорок варочная поверхность. Какой вид плиты: газовая, электрическая, индукционная и т.д. Предусмотрена ли в ней духовка.', 'format' => 'default'],
                ['key' => 'question_7_16', 'title' => 'Фартук:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, какой материал для отделки стены рабочего пространства вы хотите на кухню: пластик, МДФ, камень, стекло и т.д.', 'format' => 'default'],
                ['key' => 'question_7_17', 'title' => 'Другое:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'default'],
            ],
            8 => [
                ['key' => 'question_8_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_2', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'checkbox',   'format' => 'checkpoint'],
                ['key' => 'question_8_3', 'title' => 'Детская', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_4', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_5', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_6', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_7', 'title' => 'Кухня, объединенная с гостиной', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_8', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_9', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_10', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_11', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_12', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_13', 'title' => 'Гардеробная комната', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_8_14', 'title' => 'Другое:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'default'],
            ],
            9 => [
                ['key' => 'question_9_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_2', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'checkbox',   'format' => 'checkpoint'],
                ['key' => 'question_9_3', 'title' => 'Детская', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_4', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_5', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_6', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_7', 'title' => 'Кухня, объединенная с гостиной', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_8', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_9', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_10', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_11', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_12', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_13', 'title' => 'Гардеробная комната', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_9_14', 'title' => 'Другое:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'default'],
            ],
            10 => [
                ['key' => 'question_10_1', 'title' => 'Унитаз', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_2', 'title' => 'Раковина', 'subtitle' => '', 'type' => 'checkbox',   'format' => 'checkpoint'],
                ['key' => 'question_10_3', 'title' => 'Полотенцесушитель', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_4', 'title' => 'Мебель', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_5', 'title' => 'Водонагреватель', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_6', 'title' => 'Биде', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_7', 'title' => 'Ванна', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_8', 'title' => 'Вытяжка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_9', 'title' => 'Фильтр очистки воды', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_10', 'title' => 'Душевая кабина', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_11', 'title' => 'Гигиенический душ', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_12', 'title' => 'Стиральная машинка', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_13', 'title' => 'Система защиты от протечек', 'subtitle' => '', 'type' => 'checkbox', 'format' => 'checkpoint'],
                ['key' => 'question_10_14', 'title' => 'Другое:', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'default'],
            ],
            11 => [
                ['key' => 'question_11_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, разместить зеркало на двери, вешалку для одежды у двери.', 'format' => 'faq'],
                ['key' => 'question_11_2', 'title' => 'Детская', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу разделить пространство в детской на игровую зону и зону отдыха.', 'format' => 'faq'],
                ['key' => 'question_11_3', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, организовать пространство под хранение одежды. Установить гладильную доску и зеркало.', 'format' => 'faq'],
                ['key' => 'question_11_4', 'title' => 'Кухня и гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, на кухне вы готовите и принимаете пищу, а гостиная для особых случаем – приёма гостей. ', 'format' => 'faq'],
                ['key' => 'question_11_5', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу перегородку в совмещенном санузле.', 'format' => 'faq'],
                ['key' => 'question_11_6', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какое количество гостей вы можете принять одновременно. Как предпочитаете проводить время.', 'format' => 'faq'],
                ['key' => 'question_11_7', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу организовать рабочую зону на балконе.', 'format' => 'faq'],
                ['key' => 'question_11_8', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, совмещена ли столовая с кухней/гостиной. Как часто принимаете пищу в этой комнате?', 'format' => 'faq'],
                ['key' => 'question_11_9', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу выделить зону душа глянцевой светлой плиткой, чтобы не было видно разводов. ', 'format' => 'faq'],
                ['key' => 'question_11_10', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу кухню с барной стойкой.', 'format' => 'faq'],
                ['key' => 'question_11_11', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в кабинете разделить пространство на рабочую зону и зону для занятия спортом. ', 'format' => 'faq'],
                ['key' => 'question_11_12', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в спальне выделить уголок под домашнюю библиотеку.', 'format' => 'faq'],
                ['key' => 'question_11_13', 'title' => 'Гардеробная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, площадь комнаты.', 'format' => 'faq'],
                ['key' => 'question_11_14', 'title' => 'Другое', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'faq'],
            ],
            12 => [
                ['key' => 'question_12_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, разместить зеркало на двери, вешалку для одежды у двери.', 'format' => 'faq'],
                ['key' => 'question_12_2', 'title' => 'Детская', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу разделить пространство в детской на игровую зону и зону отдыха.', 'format' => 'faq'],
                ['key' => 'question_12_3', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, организовать пространство под хранение одежды. Установить гладильную доску и зеркало.', 'format' => 'faq'],
                ['key' => 'question_12_4', 'title' => 'Кухня и гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, на кухне вы готовите и принимаете пищу, а гостиная для особых случаем – приёма гостей. ', 'format' => 'faq'],
                ['key' => 'question_12_5', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу перегородку в совмещенном санузле.', 'format' => 'faq'],
                ['key' => 'question_12_6', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какое количество гостей вы можете принять одновременно. Как предпочитаете проводить время.', 'format' => 'faq'],
                ['key' => 'question_12_7', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу организовать рабочую зону на балконе.', 'format' => 'faq'],
                ['key' => 'question_12_8', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, совмещена ли столовая с кухней/гостиной. Как часто принимаете пищу в этой комнате?', 'format' => 'faq'],
                ['key' => 'question_12_9', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу выделить зону душа глянцевой светлой плиткой, чтобы не было видно разводов. ', 'format' => 'faq'],
                ['key' => 'question_12_10', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу кухню с барной стойкой.', 'format' => 'faq'],
                ['key' => 'question_12_11', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в кабинете разделить пространство на рабочую зону и зону для занятия спортом. ', 'format' => 'faq'],
                ['key' => 'question_12_12', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в спальне выделить уголок под домашнюю библиотеку.', 'format' => 'faq'],
                ['key' => 'question_12_13', 'title' => 'Гардеробная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, площадь комнаты.', 'format' => 'faq'],
                ['key' => 'question_12_14', 'title' => 'Другое', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'faq'],
            ],
            13 => [
                ['key' => 'question_13_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, разместить зеркало на двери, вешалку для одежды у двери.', 'format' => 'faq'],
                ['key' => 'question_13_2', 'title' => 'Детская', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу разделить пространство в детской на игровую зону и зону отдыха.', 'format' => 'faq'],
                ['key' => 'question_13_3', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, организовать пространство под хранение одежды. Установить гладильную доску и зеркало.', 'format' => 'faq'],
                ['key' => 'question_13_4', 'title' => 'Кухня и гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, на кухне вы готовите и принимаете пищу, а гостиная для особых случаем – приёма гостей. ', 'format' => 'faq'],
                ['key' => 'question_13_5', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу перегородку в совмещенном санузле.', 'format' => 'faq'],
                ['key' => 'question_13_6', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какое количество гостей вы можете принять одновременно. Как предпочитаете проводить время.', 'format' => 'faq'],
                ['key' => 'question_13_7', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу организовать рабочую зону на балконе.', 'format' => 'faq'],
                ['key' => 'question_13_8', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, совмещена ли столовая с кухней/гостиной. Как часто принимаете пищу в этой комнате?', 'format' => 'faq'],
                ['key' => 'question_13_9', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Укажите, например, хочу выделить зону душа глянцевой светлой плиткой, чтобы не было видно разводов. ', 'format' => 'faq'],
                ['key' => 'question_13_10', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Например, хочу кухню с барной стойкой.', 'format' => 'faq'],
                ['key' => 'question_13_11', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в кабинете разделить пространство на рабочую зону и зону для занятия спортом. ', 'format' => 'faq'],
                ['key' => 'question_13_12', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, хочу в спальне выделить уголок под домашнюю библиотеку.', 'format' => 'faq'],
                ['key' => 'question_13_13', 'title' => 'Гардеробная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Укажите, например, площадь комнаты.', 'format' => 'faq'],
                ['key' => 'question_13_14', 'title' => 'Другое', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Опишите, например, какие детали мы должны учесть при разработке проекта.', 'format' => 'faq'],
            ],
            14 => [
                ['key' => 'question_14_1', 'title' => 'Прихожая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_2', 'title' => 'Детская', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => ' Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_3', 'title' => 'Кладовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_4', 'title' => 'Кухня и гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_5', 'title' => 'Гостевой санузел', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_6', 'title' => 'Гостиная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_7', 'title' => 'Рабочее место', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_8', 'title' => 'Столовая', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_9', 'title' => 'Ванная комната', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_10', 'title' => 'Кухня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_11', 'title' => 'Кабинет', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_12', 'title' => 'Спальня', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_13', 'title' => 'Гардеробная', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
                ['key' => 'question_14_14', 'title' => 'Другое', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'faq'],
            ],
            15 => [
                ['key' => 'question_15_1', 'title' => 'Другое', 'subtitle' => '', 'type' => 'textarea', 'placeholder' => 'Введите желаемую сумму.', 'format' => 'default'],
            ],
        ];
        // Общие заголовки для страниц
        $titles = [
            1 => [
                'title'    => 'Информация о семье и питомцах',
                'subtitle' => 'Расскажите о членах семьи и домашних животных'
            ],
            2 => [
                'title'    => 'Мебель и перепланировка',
                'subtitle' => 'Укажите размеры имеющейся мебели и необходимость изменений'
            ],
            3 => [
                'title'    => 'Планировка помещений',
                'subtitle' => 'Выберите комнаты и зоны для будущего проекта'
            ],
            4 => [
                'title'    => 'Интерьер: стиль и предпочтения',
                'subtitle' => 'Определитесь с общим стилем и цветовыми решениями'
            ],
            5 => [
                'title'    => 'Пожелания по комплектации',
                'subtitle' => 'Опишите детали и расстановку мебели в интерьере'
            ],
            6 => [
                'title'    => 'Освещение и атмосфера',
                'subtitle' => 'Укажите пожелания по типу и яркости освещения'
            ],
            7 => [
                'title'    => 'Функциональность кухни',
                'subtitle' => 'Выберите необходимые кухонные приборы и оборудование'
            ],
            8 => [
                'title'    => 'Тёплый пол и подогрев плитки',
                'subtitle' => 'Определите помещения для установки системы обогрева'
            ],
            9 => [
                'title'    => 'Кондиционирование и вентиляция',
                'subtitle' => 'Выберите зоны для установки кондиционеров и систем вентиляции'
            ],
            10 => [
                'title'    => 'Функциональность ванной комнаты',
                'subtitle' => 'Укажите необходимую сантехнику и оборудование'
            ],
            11 => [
                'title'    => 'Напольные покрытия',
                'subtitle' => 'Выберите материалы и варианты отделки пола'
            ],
            12 => [
                'title'    => 'Освещение интерьера',
                'subtitle' => 'Определитесь с вариантами светильников и их расположением'
            ],
            13 => [
                'title'    => 'Отделка потолков',
                'subtitle' => 'Выберите тип отделки и декоративные решения для потолков'
            ],
            14 => [
                'title'    => 'Бюджет по помещениям',
                'subtitle' => 'Укажите желаемый бюджет для каждого помещения'
            ],
            15 => [
                'title'    => 'Завершающий этап',
                'subtitle' => 'Проверьте введённые данные и завершите заполнение брифа'
            ],
        ];
        
        // Находим бриф
        $brif = Common::findOrFail($id);
    
        // Проверяем, является ли пользователь ответственным за сделку
        $deal = Deal::find($brif->deal_id);
       
    
        // Если бриф активен, перенаправляем на страницу с вопросами
        if ($brif->status === 'Активный') {
            return redirect()->route('common.questions', $brif->current_page);
        }
        // Получение документов и фотографий
        $documentsPath = storage_path("app/public/brifs/{$id}/documents");
        $photosPath = storage_path("app/public/brifs/{$id}/photos");
    
        $documents = file_exists($documentsPath) ? array_diff(scandir($documentsPath), ['.', '..']) : [];
        $photos = file_exists($photosPath) ? array_diff(scandir($photosPath), ['.', '..']) : [];
    
        return view('common.show', compact(
            'brif', 'user', 'title_site', 'pageTitlesCommon', 'questions', 'documents', 'photos'
        ));
    }
    
    public function commercial_show($id)
    {
        $title_site = "Детали брифа | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();
    
        // Находим коммерческий бриф
        $brif = Commercial::findOrFail($id);
    
        // Проверяем, является ли пользователь ответственным за сделку
        $deal = Deal::find($brif->deal_id);
    
        // Получаем дополнительные данные, такие как зоны и предпочтения
        $zones = $brif && $brif->zones ? json_decode($brif->zones, true) : [];
        $preferences = $brif && $brif->preferences ? json_decode($brif->preferences, true) : [];
        $documents = $brif && $brif->documents ? json_decode($brif->documents, true) : []; // Декодируем документы
    
        // Вопросы для отображения
        $questions = [
            1 => "Зоны и их функционал",
            2 => "Метраж зон",
            3 => "Зоны и их стиль оформления",
            4 => "Мебилировка зон",
            5 => "Предпочтения отделочных материалов",
            6 => "Освещение зон",
            7 => "Кондиционирование зон",
            8 => "Напольное покрытие зон",
            9 => "Отделка стен зон",
            10 => "Отделка потолков зон",
            11 => "Категорически неприемлемо или нет",
            12 => "Бюджет на помещения",
            13 => "Пожелания и комментарии",
        ];
    
        // Формируем предпочтения с названиями вопросов
        $preferencesFormatted = [];
        foreach ($zones as $index => $zone) {
            $zoneName = $zone['name'] ?? "Без названия"; // Берем название зоны
            $preferencesFormatted[$zoneName] = [];      // Используем его как ключ
            if (isset($preferences["zone_$index"])) {
                foreach ($preferences["zone_$index"] as $questionKey => $answer) {
                    $questionNumber = str_replace('question_', '', $questionKey);
                    $questionTitle = $questions[$questionNumber] ?? "Вопрос $questionNumber";
                    $preferencesFormatted[$zoneName][] = [
                        'question' => $questionTitle,
                        'answer' => $answer,
                    ];
                }
            }
        }
    
        return view('commercial.show', compact('brif', 'user', 'title_site', 'zones', 'preferencesFormatted', 'documents'));
    }
        
    public function commercial_store(Request $request)
    {
        $brif = Commercial::create([
            'title' => 'Коммерческий бриф',
            'description' => 'Коммерческий бриф ориентирован на детальную проработку проектов для коммерческих объектов. В его рамках рассматриваются вопросы, связанные с функциональностью, зонированием, метражами, отделкой и техническими требованиями помещений. Вы указываете предпочтения по материалам, мебельной компоновке, освещению и системам кондиционирования, а также устанавливаете бюджет проекта. Такой бриф помогает разработать комплексное решение, отвечающее высоким стандартам эффективности, комфорта и эстетики, что особенно важно для коммерческих пространств.',
            'status' => 'Активный',
            'article' => Str::random(15), // Генерация случайной строки
            'user_id' => auth()->id(), // Привязка к текущему пользователю
        ]);
        return redirect()->route('commercial.questions', [
            'id'   => $brif->id,
            'page' => 1
        ]);
       
    }
    /**
     * Отображение конкретного брифа.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function store(Request $request)
    {
        $type = $request->input('brif_type');
    
        if ($type === 'common') {
            // Создание общего брифа
            $brif = Common::create([
                'title'       => 'Общий бриф',
                'description' => 'Общий бриф представляет собой структурированный опрос, направленный на выявление базовых предпочтений и потребностей клиента. Он включает вопросы о личных предпочтениях, функциональных особенностях помещения, количестве жильцов, а также об основных стилевых решениях. Заполнение этого брифа позволяет нам лучше понять ваш образ жизни и создать проект, который гармонично объединит практичность, уют и эстетическое единство интерьера.',
                'status'      => 'Активный',
                'article'     => Str::random(15),
                'user_id'     => auth()->id(),
            ]);
    
            return redirect()->route('common.questions', [
                'id'   => $brif->id,
                'page' => 1
            ]);
        } elseif ($type === 'commercial') {
            // Создание коммерческого брифа
            $brif = Commercial::create([
                'title'       => 'Коммерческий бриф',
                'description' => 'Коммерческий бриф ориентирован на детальную проработку проектов для коммерческих объектов. В его рамках рассматриваются вопросы, связанные с функциональностью, зонированием, метражами, отделкой и техническими требованиями помещений. Вы указываете предпочтения по материалам, мебельной компоновке, освещению и системам кондиционирования, а также устанавливаете бюджет проекта. Такой бриф помогает разработать комплексное решение, отвечающее высоким стандартам эффективности, комфорта и эстетики, что особенно важно для коммерческих пространств.',
                'status'      => 'Активный',
                'article'     => Str::random(15),
                'user_id'     => auth()->id(),
            ]);
    
            return redirect()->route('commercial.questions', [
                'id'   => $brif->id,
                'page' => 1
            ]);
        }
    
        // На случай, если по каким-то причинам brif_type не отправилось
        return redirect()->back()->with('error', 'Не удалось определить тип брифа.');
    }
    public function destroy($id)
{
    // Попытка найти бриф среди Общих и Коммерческих
    $brif = \App\Models\Common::find($id) ?: \App\Models\Commercial::find($id);
    
    // Проверяем, принадлежит ли бриф текущему пользователю
    if ($brif && $brif->user_id == auth()->id()) {
        $brif->delete();
        return redirect()->back()->with('success', 'Бриф успешно удалён');
    }
    
    return redirect()->back()->with('error', 'Удалить бриф не удалось');
}

}
