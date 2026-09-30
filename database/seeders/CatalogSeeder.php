<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Demo catalog content. Category names mirror lang/{uz,ru}/site.php's
 * `categories` keys, and product names are real Winson model names pulled
 * from winsonchina.com's listings (see .claude/winsonchina-reference.md) —
 * only the short descriptions here are original placeholder copy pending
 * real catalog data from the content-importer workflow.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'handheld',
                'name_uz' => 'Qo\'lda ushlab turiladigan shtrix-kod skaneri',
                'name_ru' => 'Ручной сканер штрих-кода',
                'products' => [
                    [
                        'slug' => 'wnl-7000g',
                        'name' => 'Winson WNL-7000G',
                        'sensor_type' => 'laser',
                        'desc_uz' => 'Simsiz, 1D lazer skaner — do\'kon kassalari va kichik omborlar uchun ixcham va yengil yechim.',
                        'desc_ru' => 'Беспроводной 1D лазерный сканер — компактное и лёгкое решение для касс и небольших складов.',
                    ],
                    [
                        'slug' => 'wni-9610',
                        'name' => 'Winson WNI-9610',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'Simli 2D imij skaner — QR-kodlar va shikastlangan shtrix-kodlarni ham ishonchli o\'qiydi.',
                        'desc_ru' => 'Проводной 2D имидж-сканер — уверенно считывает QR-коды и повреждённые штрих-коды.',
                    ],
                ],
            ],
            [
                'slug' => 'industrial',
                'name_uz' => 'Sanoat shtrix-kod skaneri',
                'name_ru' => 'Промышленный сканер штрих-кода',
                'products' => [
                    [
                        'slug' => 'st10-71',
                        'name' => 'Winson ST10-71',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'Simsiz Bluetooth sanoat skaneri, quvvatlash bazasi bilan — changga va tushishga chidamli korpusda.',
                        'desc_ru' => 'Беспроводной промышленный сканер Bluetooth с базой подзарядки — в пыле- и ударопрочном корпусе.',
                    ],
                    [
                        'slug' => 'st10-70-ip65',
                        'name' => 'Winson ST10-70 IP65',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'IP65 himoya darajali simli sanoat skaneri — chang va suv purkashlariga bardosh beradi.',
                        'desc_ru' => 'Проводной промышленный сканер с защитой IP65 — устойчив к пыли и брызгам воды.',
                    ],
                ],
            ],
            [
                'slug' => 'desktop',
                'name_uz' => 'Stol usti shtrix-kod skaneri',
                'name_ru' => 'Настольный сканер штрих-кода',
                'products' => [
                    [
                        'slug' => 'wai-6770',
                        'name' => 'Winson WAI-6770',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'Oq yorug\'lik bilan ishlaydigan 2D CMOS stol usti skaner — qo\'lni band qilmasdan tez skanerlash.',
                        'desc_ru' => 'Настольный 2D CMOS-сканер с белой подсветкой — быстрое сканирование без задействования рук.',
                    ],
                    [
                        'slug' => 'wai-6780',
                        'name' => 'Winson WAI-6780',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'Har tomonlama (omni-directional) 1D/2D stol usti skaner — kassa aylanmasini tezlashtiradi.',
                        'desc_ru' => 'Всенаправленный (omni-directional) настольный сканер 1D/2D — ускоряет работу кассы.',
                    ],
                ],
            ],
            [
                'slug' => 'scan_engine',
                'name_uz' => 'Shtrix-kod skanerlash moduli',
                'name_ru' => 'Сканирующий модуль',
                'products' => [
                    [
                        'slug' => 'wdc-3000',
                        'name' => 'Winson WDC-3000',
                        'sensor_type' => 'laser',
                        'desc_uz' => '1D o\'rnatiladigan skanerlash moduli — o\'z qurilmangizga integratsiya qilish uchun.',
                        'desc_ru' => 'Встраиваемый 1D сканирующий модуль — для интеграции в собственное устройство.',
                    ],
                    [
                        'slug' => 'wdi3038',
                        'name' => 'Winson WDI3038',
                        'sensor_type' => 'cmos',
                        'desc_uz' => '1D/2D OEM skanerlash moduli — kiosk, POS va o\'z-o\'ziga xizmat ko\'rsatish terminallari uchun.',
                        'desc_ru' => 'OEM сканирующий модуль 1D/2D — для киосков, POS и терминалов самообслуживания.',
                    ],
                ],
            ],
            [
                'slug' => 'fix_mounted',
                'name_uz' => 'Statsionar (mahkamlangan) skaner',
                'name_ru' => 'Стационарный сканер',
                'products' => [
                    [
                        'slug' => 'wgl-1010',
                        'name' => 'Winson WGL-1010',
                        'sensor_type' => 'laser',
                        'desc_uz' => '1D lazerli statsionar skaner — konveyer va o\'z-o\'ziga xizmat ko\'rsatish kassalari uchun.',
                        'desc_ru' => 'Стационарный 1D лазерный сканер — для конвейеров и касс самообслуживания.',
                    ],
                    [
                        'slug' => 'wgi-3220',
                        'name' => 'Winson WGI-3220',
                        'sensor_type' => 'cmos',
                        'desc_uz' => '1D/2D statsionar skaner — telefon ekranidagi kodlarni ham qiynalmay o\'qiydi.',
                        'desc_ru' => 'Стационарный сканер 1D/2D — легко считывает коды прямо с экрана телефона.',
                    ],
                ],
            ],
            [
                'slug' => 'embedded',
                'name_uz' => 'O\'rnatiladigan (embedded) skaner',
                'name_ru' => 'Встраиваемый сканер',
                'products' => [
                    [
                        'slug' => 'z2-670',
                        'name' => 'Winson Z2-670',
                        'sensor_type' => 'cmos',
                        'desc_uz' => '1D/2D o\'rnatiladigan skaner moduli — chipta darvozalari va avtomatlashtirilgan tizimlar uchun.',
                        'desc_ru' => 'Встраиваемый модуль 1D/2D — для турникетов и автоматизированных систем.',
                    ],
                    [
                        'slug' => 'z4p-520',
                        'name' => 'Winson Z4P-520',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'Keng burchakli QR skaner moduli — avtomatik chipta darvozalarida tez o\'tishni ta\'minlaydi.',
                        'desc_ru' => 'Широкоугольный QR-модуль — обеспечивает быстрый проход через автоматические турникеты.',
                    ],
                ],
            ],
            [
                'slug' => 'pda',
                'name_uz' => 'Ma\'lumot yig\'uvchi PDA',
                'name_ru' => 'Терминал сбора данных (ТСД)',
                'products' => [
                    [
                        'slug' => 'winny-pro',
                        'name' => 'Winson WINNY Pro',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'IP54 himoyali ixcham rugged PDA — ombor va dala sharoitida kundalik ishlatish uchun.',
                        'desc_ru' => 'Компактный защищённый (IP54) ТСД — для повседневной работы на складе и в поле.',
                    ],
                    [
                        'slug' => 'wpc-9082hc',
                        'name' => 'Winson WPC-9082HC',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'Tibbiyot muassasalari uchun Android PDA — bemor va dori-darmon nazoratini soddalashtiradi.',
                        'desc_ru' => 'Android ТСД для медицинских учреждений — упрощает контроль пациентов и лекарств.',
                    ],
                ],
            ],
            [
                'slug' => 'smart_terminal',
                'name_uz' => 'Aqlli terminal',
                'name_ru' => 'Смарт-терминал',
                'products' => [
                    [
                        'slug' => 'kiosk-15-6-pos',
                        'name' => '15.6" Kiosk POS Terminal',
                        'sensor_type' => null,
                        'desc_uz' => 'Termoprinterli 15.6" sensorli kiosk terminal — o\'z-o\'ziga xizmat ko\'rsatish uchun.',
                        'desc_ru' => '15.6" сенсорный киоск-терминал с термопринтером — для самообслуживания.',
                    ],
                    [
                        'slug' => 'price-checker-win10',
                        'name' => 'Win10 Price Checker',
                        'sensor_type' => 'cmos',
                        'desc_uz' => 'Sig\'imli sensorli narx tekshiruvchi terminal — xaridorlar mustaqil narxni bilishi uchun.',
                        'desc_ru' => 'Терминал проверки цен с ёмкостным сенсорным экраном — покупатели сами узнают цену.',
                    ],
                ],
            ],
        ];

        foreach ($categories as $index => $category) {
            $cat = Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name_uz' => $category['name_uz'],
                    'name_ru' => $category['name_ru'],
                    'sort_order' => $index,
                ]
            );

            foreach ($category['products'] as $pIndex => $product) {
                Product::updateOrCreate(
                    ['slug' => $product['slug']],
                    [
                        'category_id' => $cat->id,
                        'name_uz' => $product['name'],
                        'name_ru' => $product['name'],
                        'description_uz' => $product['desc_uz'],
                        'description_ru' => $product['desc_ru'],
                        'sensor_type' => $product['sensor_type'],
                        'image' => "images/products/{$category['slug']}.svg",
                        'sort_order' => $pIndex,
                    ]
                );
            }
        }
    }
}
