<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Common;
use App\Models\Deal;
use Illuminate\Support\Facades\Http;

class CommonController extends Controller
{
    /**
     * CommonController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Отображение формы с вопросами "Общего" брифа.
     *
     * @param  int  $id    ID конкретного брифа
     * @param  int  $page  Номер страницы (шаг) с вопросами
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function questions($id, $page)
    {
        // Пытаемся найти бриф по ID и по текущему пользователю
        $brif = Common::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$brif) {
            return redirect()->route('brifs.index')
                ->with('error', 'Бриф не найден или не принадлежит данному пользователю.');
        }
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
    

    // Если указанная страница не существует
    if (!isset($questions[$page])) {
        return redirect()->route('brifs.index')
            ->with('error', 'Неверный номер страницы вопросов.');
    }

    $title_site = "Процесс создания Общего брифа | Личный кабинет Экспресс-дизайн";
    $user = Auth::user();
    $totalPages = count($questions);
    return view('common.questions', [
        'questions' => $questions[$page],
        'page'      => $page,
        'user'      => $user,
        'totalPages'=> $totalPages,
        'brif'      => $brif, // Now correctly passed as $brif
        'title'     => $titles[$page]['title'] ?? '',
        'subtitle'  => $titles[$page]['subtitle'] ?? '',
        'title_site'=> $title_site
    ]);
    
    
    }
  /**
     * Сохранение ответов для указанного брифа на конкретной странице.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id    ID конкретного брифа
     * @param  int  $page  Текущая страница (шаг)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveAnswers(Request $request, $id, $page)
    {
        // Валидация входящих данных
        $data = $request->validate([
            'answers'      => 'nullable|array',
            'price'        => 'nullable|numeric',
            'documents'    => 'nullable|array',
            'documents.*'  => 'file|max:25600|mimes:pdf,xlsx,xls,doc,docx,jpg,jpeg,png,heic,heif'
        ]);
    
        // Находим бриф по ID и по текущему пользователю
        $brif = Common::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
    
        if (!$brif) {
            return redirect()->route('brifs.index')
                ->with('error', 'Бриф не найден или не принадлежит данному пользователю.');
        }
    
        // Если передано поле price — обновляем его
        if (isset($data['price'])) {
            $brif->price = $data['price'];
        }
    
        // Обновляем ответы в соответствующих колонках таблицы
        if (isset($data['answers'])) {
            foreach ($data['answers'] as $key => $answer) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('commons', $key)) {
                    $brif->$key = $answer;
                }
            }
        }
    
        // Если это страница 15 — обработка загрузки файлов
        if ($page == 15 && $request->hasFile('documents')) {
            $uploadedFiles = [];
            $totalSize = 0;
            $userId = auth()->id();
    
            foreach ($request->file('documents') as $file) {
                if ($file->isValid()) {
                    $fileSize = $file->getSize();
                    $totalSize += $fileSize;
    
                    if ($totalSize > 25 * 1024 * 1024) {
                        return redirect()->back()->with('error', 'Суммарный размер файлов не должен превышать 25 МБ.');
                    }
    
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $briefId = $brif->id;
                    $directory = public_path("uploads/documents/user/{$userId}/commons/{$briefId}");
    
                    if (!file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }
    
                    $file->move($directory, $filename);
                    $uploadedFiles[] = "uploads/documents/user/{$userId}/commons/{$briefId}/{$filename}";
                } else {
                    return redirect()->back()->with('error', 'Один или несколько файлов имеют неверный формат.');
                }
            }
    
            if (!empty($uploadedFiles)) {
                $existingDocuments = $brif->documents ? json_decode($brif->documents, true) : [];
                $brif->documents = json_encode(array_merge($existingDocuments, $uploadedFiles));
            }
        }
    
        // Обработка действия «назад»
        if ($request->input('action') === 'prev') {
            $prevPage = $page > 1 ? $page - 1 : 1;
            $brif->current_page = $prevPage;
            $brif->save();
            return redirect()->route('common.questions', ['id' => $brif->id, 'page' => $prevPage]);
        }
    
        // Массив для определения, есть ли следующая страница
        $questionsMapping = [
            1  => ['question_1_1', 'question_1_2'],
            2  => ['question_2_1', 'question_2_2'],
            3  => ['question_3_1', 'question_3_2'],
            4  => ['question_4_1', 'question_4_2'],
            5  => ['question_5_1', 'question_5_2'],
            6  => ['question_6_1', 'question_6_2'],
            7  => ['question_7_1', 'question_7_2'],
            8  => ['question_8_1', 'question_8_2'],
            9  => ['question_9_1', 'question_9_2'],
            10 => ['question_10_1', 'question_10_2'],
            11 => ['question_11_1', 'question_11_2'],
            12 => ['question_12_1', 'question_12_2'],
            13 => ['question_13_1', 'question_13_2'],
            14 => ['question_14_1', 'question_14_2'],
            15 => ['question_15_1', 'question_15_2'],
        ];
    
        $nextPage = $page + 1;
        // Если следующей страницы нет — завершаем заполнение брифа
        if (!isset($questionsMapping[$nextPage])) {
            $brif->status = 'Завершенный';
            $brif->save();
    
            // Поиск (или создание) сделки и привязка к брифу
            $deal = Deal::where('user_id', auth()->id())->first();
            if ($deal) {
                $brif->deal_id = $deal->id;
                $brif->save();
    
                $deal->common_id = $brif->id;
                $deal->update([
                    'client_name'   => auth()->user()->name,
                    'client_phone'  => auth()->user()->phone ?? 'N/A',
                    'total_sum'     => $brif->price ?? 0,
                    'status'        => 'Бриф прикриплен',
                    'link'          => "/common/{$brif->id}",
                ]);
            }
    
            return redirect()->route('brifs.index')
                ->with('success', 'Бриф успешно заполнен!');
        }
    
        // Иначе — сохраняем номер следующей страницы и перенаправляем пользователя туда
        $brif->current_page = $nextPage;
        $brif->save();
    
        return redirect()->route('common.questions', ['id' => $brif->id, 'page' => $nextPage]);
    }
    
    
}
