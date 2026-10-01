<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

/**
 * The standard characteristics of barcode scanners, data collection
 * terminals and smart terminals (uz/ru). Idempotent: updates by `code`, so it
 * can be re-run; admins can edit/extend everything in /admin → Features.
 *
 * Format: [code, group, type, name_uz, name_ru, unit_uz, unit_ru, filterable, options]
 * options: [code => [label_uz, label_ru]]
 */
class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as $i => [$code, $group, $type, $nameUz, $nameRu, $unitUz, $unitRu, $filterable, $options]) {
            $feature = Feature::updateOrCreate(['code' => $code], [
                'group' => $group,
                'type' => $type,
                'name_uz' => $nameUz,
                'name_ru' => $nameRu,
                'unit_uz' => $unitUz,
                'unit_ru' => $unitRu,
                'is_filterable' => $filterable,
                'sort_order' => $i,
            ]);

            $j = 0;
            foreach ($options as $optCode => [$labelUz, $labelRu]) {
                $feature->options()->updateOrCreate(['code' => $optCode], [
                    'label_uz' => $labelUz,
                    'label_ru' => $labelRu,
                    'sort_order' => $j++,
                ]);
            }
        }
    }

    private function definitions(): array
    {
        return [
            // ---- Scanning ----
            ['code_dimension', 'scanning', 'select', 'O\'qiladigan kodlar', 'Типы кодов', null, null, true, [
                '1d' => ['Faqat 1D (chiziqli)', 'Только 1D (линейные)'],
                '1d2d' => ['1D va 2D (QR, Data Matrix)', '1D и 2D (QR, Data Matrix)'],
            ]],
            ['sensor', 'scanning', 'select', 'Sensor turi', 'Тип сенсора', null, null, true, [
                'laser' => ['Lazer', 'Лазер'],
                'ccd' => ['CCD (chiziqli imijer)', 'CCD (линейный имиджер)'],
                'cmos' => ['CMOS (2D imijer)', 'CMOS (2D-имиджер)'],
            ]],
            ['scan_pattern', 'scanning', 'select', 'Skanerlash naqshi', 'Шаблон сканирования', null, null, true, [
                'single_line' => ['Bitta chiziq', 'Одна линия'],
                'multi_line' => ['Ko\'p chiziqli (omni)', 'Многоплоскостной (omni)'],
                'area' => ['Maydon (tasvir)', 'Площадной (изображение)'],
            ]],
            ['scan_modes', 'scanning', 'multi', 'Skanerlash rejimlari', 'Режимы сканирования', null, null, true, [
                'trigger' => ['Tugma (trigger)', 'По кнопке (триггер)'],
                'auto_sense' => ['Avtomatik (presentation)', 'Автоматический (presentation)'],
                'continuous' => ['Uzluksiz', 'Непрерывный'],
                'command' => ['Buyruq orqali', 'По команде'],
            ]],
            ['symbologies', 'scanning', 'multi', 'Qo\'llab-quvvatlanadigan kodlar', 'Поддерживаемые символики', null, null, true, [
                'ean_upc' => ['EAN / UPC', 'EAN / UPC'],
                'code128' => ['Code 128 / GS1-128', 'Code 128 / GS1-128'],
                'code39' => ['Code 39', 'Code 39'],
                'code93' => ['Code 93', 'Code 93'],
                'codabar' => ['Codabar', 'Codabar'],
                'itf' => ['Interleaved 2 of 5', 'Interleaved 2 of 5'],
                'gs1_databar' => ['GS1 DataBar', 'GS1 DataBar'],
                'qr' => ['QR Code', 'QR Code'],
                'datamatrix' => ['Data Matrix', 'Data Matrix'],
                'pdf417' => ['PDF417', 'PDF417'],
                'aztec' => ['Aztec', 'Aztec'],
                'maxicode' => ['MaxiCode', 'MaxiCode'],
                'han_xin' => ['Han Xin', 'Han Xin'],
            ]],
            ['reads_screens', 'scanning', 'boolean', 'Telefon/monitor ekranidan o\'qiydi', 'Читает с экрана телефона/монитора', null, null, true, []],
            ['reads_damaged', 'scanning', 'boolean', 'Shikastlangan va xira kodlarni o\'qiydi', 'Читает повреждённые и бледные коды', null, null, true, []],
            ['dpm', 'scanning', 'boolean', 'DPM (metallga o\'yilgan kodlar)', 'DPM (коды на металле)', null, null, true, []],
            ['scan_rate', 'scanning', 'number', 'Skanerlash tezligi', 'Скорость сканирования', 'skan/s', 'скан/с', true, []],
            ['resolution', 'scanning', 'number', 'Minimal ruxsat (1D)', 'Минимальное разрешение (1D)', 'mil', 'mil', false, []],
            ['read_distance', 'scanning', 'number', 'Maksimal o\'qish masofasi', 'Максимальная дистанция чтения', 'sm', 'см', true, []],
            ['sensor_resolution', 'scanning', 'select', 'Sensor ruxsati', 'Разрешение сенсора', null, null, false, [
                '640x480' => ['640×480', '640×480'],
                '752x480' => ['752×480', '752×480'],
                '1280x800' => ['1280×800 (1 MP)', '1280×800 (1 Мп)'],
                '1920x1080' => ['1920×1080 (2 MP)', '1920×1080 (2 Мп)'],
            ]],
            ['aiming', 'scanning', 'select', 'Nishon (aimer)', 'Прицел', null, null, false, [
                'red_led' => ['Qizil LED', 'Красный LED'],
                'red_laser' => ['Qizil lazer', 'Красный лазер'],
                'none' => ['Yo\'q', 'Нет'],
            ]],

            // ---- Connectivity ----
            ['connection', 'connectivity', 'select', 'Ulanish turi', 'Тип подключения', null, null, true, [
                'wired' => ['Simli', 'Проводной'],
                'wireless' => ['Simsiz', 'Беспроводной'],
                'both' => ['Simli va simsiz', 'Проводной и беспроводной'],
            ]],
            ['interfaces', 'connectivity', 'multi', 'Interfeyslar', 'Интерфейсы', null, null, true, [
                'usb_hid' => ['USB-HID (klaviatura)', 'USB-HID (клавиатура)'],
                'usb_com' => ['USB-COM (virtual COM)', 'USB-COM (виртуальный COM)'],
                'rs232' => ['RS-232', 'RS-232'],
                'ps2' => ['PS/2 (keyboard wedge)', 'PS/2 (keyboard wedge)'],
                'ttl' => ['TTL / UART', 'TTL / UART'],
                'ethernet' => ['Ethernet', 'Ethernet'],
                'usb_c' => ['USB Type-C', 'USB Type-C'],
            ]],
            ['wireless', 'connectivity', 'multi', 'Simsiz texnologiyalar', 'Беспроводные технологии', null, null, true, [
                'bluetooth' => ['Bluetooth', 'Bluetooth'],
                'radio_24' => ['2.4 GHz radio', 'Радио 2,4 ГГц'],
                'wifi' => ['Wi-Fi', 'Wi-Fi'],
                'lte' => ['4G / LTE', '4G / LTE'],
                'nfc' => ['NFC', 'NFC'],
                'gps' => ['GPS', 'GPS'],
            ]],
            ['wireless_range', 'connectivity', 'number', 'Simsiz aloqa masofasi', 'Дальность беспроводной связи', 'm', 'м', true, []],
            ['offline_memory', 'connectivity', 'boolean', 'Oflayn xotira (batch rejimi)', 'Офлайн-память (пакетный режим)', null, null, true, []],
            ['cradle', 'connectivity', 'boolean', 'Zaryadlash bazasi (stakan)', 'Зарядная база (подставка)', null, null, true, []],

            // ---- Durability ----
            ['ip_rating', 'durability', 'select', 'Himoya darajasi (IP)', 'Степень защиты (IP)', null, null, true, [
                'ip42' => ['IP42', 'IP42'],
                'ip52' => ['IP52', 'IP52'],
                'ip54' => ['IP54', 'IP54'],
                'ip65' => ['IP65', 'IP65'],
                'ip67' => ['IP67', 'IP67'],
                'ip68' => ['IP68', 'IP68'],
            ]],
            ['drop_height', 'durability', 'number', 'Tushishga chidamlilik', 'Устойчивость к падениям', 'm', 'м', true, []],
            ['operating_temp_min', 'durability', 'number', 'Ish harorati (min)', 'Рабочая температура (мин)', '°C', '°C', false, []],
            ['operating_temp_max', 'durability', 'number', 'Ish harorati (max)', 'Рабочая температура (макс)', '°C', '°C', false, []],
            ['disinfectant_ready', 'durability', 'boolean', 'Dezinfeksiyaga chidamli korpus', 'Корпус устойчив к дезинфекции', null, null, true, []],

            // ---- Power ----
            ['power_source', 'power', 'multi', 'Quvvat manbai', 'Источник питания', null, null, true, [
                'usb' => ['USB orqali', 'От USB'],
                'battery' => ['Akkumulyator', 'Аккумулятор'],
                'adapter' => ['Tashqi adapter', 'Внешний адаптер'],
                'poe' => ['PoE', 'PoE'],
            ]],
            ['battery_capacity', 'power', 'number', 'Akkumulyator sig\'imi', 'Ёмкость аккумулятора', 'mA·soat', 'мА·ч', true, []],
            ['scans_per_charge', 'power', 'number', 'Bir zaryadda skanlar', 'Сканирований на одном заряде', 'ta', 'шт', false, []],

            // ---- Device (PDA, terminals) ----
            ['os', 'device', 'select', 'Operatsion tizim', 'Операционная система', null, null, true, [
                'android' => ['Android', 'Android'],
                'windows' => ['Windows', 'Windows'],
                'linux' => ['Linux', 'Linux'],
                'none' => ['Yo\'q', 'Нет'],
            ]],
            ['screen_size', 'device', 'number', 'Ekran diagonali', 'Диагональ экрана', 'dyuym', 'дюйм', true, []],
            ['touchscreen', 'device', 'boolean', 'Sensorli ekran', 'Сенсорный экран', null, null, true, []],
            ['keypad', 'device', 'select', 'Klaviatura', 'Клавиатура', null, null, true, [
                'numeric' => ['Raqamli', 'Цифровая'],
                'full' => ['To\'liq (QWERTY)', 'Полная (QWERTY)'],
                'none' => ['Yo\'q (faqat ekran)', 'Нет (только экран)'],
            ]],
            ['ram', 'device', 'number', 'Operativ xotira (RAM)', 'Оперативная память (RAM)', 'GB', 'ГБ', true, []],
            ['storage', 'device', 'number', 'Doimiy xotira', 'Встроенная память', 'GB', 'ГБ', true, []],
            ['camera', 'device', 'boolean', 'Kamera', 'Камера', null, null, false, []],
            ['printer', 'device', 'boolean', 'Ichki printer', 'Встроенный принтер', null, null, true, []],

            // ---- Other ----
            ['mounting', 'other', 'multi', 'O\'rnatish usuli', 'Способ установки', null, null, true, [
                'handheld' => ['Qo\'lda', 'Ручной'],
                'stand' => ['Stendda (hands-free)', 'На подставке (hands-free)'],
                'desktop' => ['Stol ustida', 'Настольный'],
                'wall' => ['Devorga', 'Настенный'],
                'panel' => ['Panelga o\'rnatiladi', 'Встраивается в панель'],
                'fixed' => ['Statsionar (kronshteyn)', 'Стационарный (кронштейн)'],
            ]],
            ['indicators', 'other', 'multi', 'Indikatsiya', 'Индикация', null, null, false, [
                'beeper' => ['Ovozli signal', 'Звуковой сигнал'],
                'led' => ['LED', 'LED'],
                'vibration' => ['Tebranish', 'Вибрация'],
            ]],
            ['weight', 'other', 'number', 'Og\'irligi', 'Вес', 'g', 'г', false, []],
            ['warranty', 'other', 'number', 'Kafolat', 'Гарантия', 'oy', 'мес.', true, []],
        ];
    }
}
