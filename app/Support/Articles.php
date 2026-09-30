<?php

namespace App\Support;

/**
 * Seed source only: the original hand-written News articles, loaded into the
 * `articles` table by ArticleSeeder. Don't read this at request time — the DB
 * (edited via /admin) is the source of truth once seeded.
 *
 * Each article: slug, image (public/images/news/*.svg), published_at,
 * read_minutes, and uz/ru => [title, excerpt, body (array of paragraphs)].
 */
class Articles
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return [
            [
                'slug' => '1d-vs-2d-barcodes',
                'image' => 'images/news/1d-vs-2d-barcodes.svg',
                'published_at' => '2026-08-03',
                'read_minutes' => 4,
                'uz' => [
                    'title' => '1D va 2D shtrix-kodlar: farqi nimada?',
                    'excerpt' => 'Chiziqlardan iborat oddiy shtrix-kod bilan kvadrat QR-kod nimasi bilan farq qiladi va qachon qaysi birini tanlash kerak.',
                    'body' => [
                        '1D (chiziqli) shtrix-kod ma\'lumotni parallel chiziqlar va bo\'shliqlar kengligida kodlaydi va faqat bitta yo\'nalishda — kesib o\'tuvchi chiziq bo\'ylab — o\'qiladi. U odatda 20-25 ta belgigacha sig\'diradi, shuning uchun ko\'pincha shunchaki mahsulot raqamini saqlaydi, qolgan barcha ma\'lumot (nomi, narxi) do\'kon bazasidan olinadi.',
                        '2D shtrix-kod (masalan, QR-kod yoki Data Matrix) ma\'lumotni ikki o\'lchamli — qator va ustunlardan iborat — naqshda saqlaydi. Bu unga bir necha ming belgigacha matn, havola yoki boshqa ma\'lumotni to\'g\'ridan-to\'g\'ri o\'zida saqlash imkonini beradi, bazaga murojaat qilmasdan.',
                        '2D kodlarning yana bir afzalligi — xatolarni tuzatish algoritmi. Kodning bir qismi iflos, yirtilgan yoki yorug\'lik tushib turgan bo\'lsa ham, skaner qolgan qismidan ma\'lumotni tiklay oladi. 1D kodlarda bunday zaxira deyarli yo\'q.',
                        'Amalda: chakana savdo kassalarida, ombordagi standart mahsulotlarda ko\'pincha 1D kod yetarli va arzon. QR-kod esa reklama havolalari, chipta va bordingpasslar, shuningdek kichik yoki tez-tez shikastlanadigan buyumlarni belgilash uchun qulayroq.',
                        'Shu sababli Winson katalogida ham 1D lazer/CCD skanerlar, ham 2D CMOS skanerlar mavjud — qaysi turdagi kodlar bilan ishlashingizga qarab to\'g\'ri modelni tanlash muhim.',
                    ],
                ],
                'ru' => [
                    'title' => '1D и 2D штрих-коды: в чём разница?',
                    'excerpt' => 'Чем обычный полосатый штрих-код отличается от квадратного QR-кода и когда что выбирать.',
                    'body' => [
                        '1D (линейный) штрих-код кодирует данные шириной параллельных линий и промежутков между ними и считывается только в одном направлении — поперёк линий. Обычно он вмещает до 20-25 символов, поэтому чаще всего хранит просто номер товара, а остальные данные (название, цена) берутся из базы магазина.',
                        '2D штрих-код (например, QR-код или Data Matrix) хранит данные в двумерном узоре из строк и столбцов. Это позволяет вместить до нескольких тысяч символов текста, ссылку или другую информацию прямо в самом коде, без обращения к базе данных.',
                        'Ещё одно преимущество 2D-кодов — алгоритм коррекции ошибок. Даже если часть кода загрязнена, порвана или на неё падает блик, сканер может восстановить данные по оставшейся части. У 1D-кодов такого запаса прочности почти нет.',
                        'На практике: на кассах розничной торговли и для стандартных складских товаров обычно достаточно недорогого 1D-кода. QR-код же удобнее для рекламных ссылок, билетов и посадочных талонов, а также для маркировки мелких или часто повреждаемых изделий.',
                        'Поэтому в каталоге Winson есть и 1D лазерные/CCD сканеры, и 2D CMOS сканеры — важно выбрать модель под те коды, с которыми вы реально работаете.',
                    ],
                ],
            ],
            [
                'slug' => 'linear-barcode-symbologies',
                'image' => 'images/news/linear-barcode-symbologies.svg',
                'published_at' => '2026-08-10',
                'read_minutes' => 4,
                'uz' => [
                    'title' => 'Chiziqli shtrix-kod turlari: UPC, EAN, Code 39 va Code 128',
                    'excerpt' => 'Har kuni ko\'radigan shtrix-kodlarning to\'rtta asosiy "tili" va ular qayerda ishlatilishi.',
                    'body' => [
                        'UPC-A — Shimoliy Amerikada keng tarqalgan 12 xonali raqamli format, chakana savdoda mahsulotni bir ma\'noli aniqlash uchun ishlatiladi.',
                        'EAN-13 — UPC ning xalqaro "ukasi", 13 xonadan iborat va birinchi raqamlar mamlakat kodini bildiradi. Deyarli barcha dunyo bo\'ylab do\'kon javonlaridagi mahsulotlarda shu format bor.',
                        'Code 39 — harflar va raqamlarni birga kodlay oladigan, o\'z-o\'zini tekshiruvchi format. Sodda va ishonchli bo\'lgani uchun logistika, avtomobil sanoati va davlat/harbiy tizimlarda hali ham keng qo\'llaniladi.',
                        'Code 128 — yuqori zichlikdagi alifbo-raqamli format, bir xil uzunlikdagi chiziqqa ko\'proq ma\'lumot sig\'diradi. Uning GS1-128 varianti butun ta\'minot zanjiri (yetkazib berish, ombor, transport) bo\'ylab standart hisoblanadi.',
                        'To\'g\'ri skanerni tanlash uchun avval qaysi formatlar bilan ishlashingizni bilish kerak — aksariyat zamonaviy CCD va lazer skanerlar bu to\'rtta formatning barchasini muammosiz o\'qiydi.',
                    ],
                ],
                'ru' => [
                    'title' => 'Типы линейных штрих-кодов: UPC, EAN, Code 39 и Code 128',
                    'excerpt' => 'Четыре основных «языка» штрих-кодов, которые вы видите каждый день, и где они применяются.',
                    'body' => [
                        'UPC-A — распространённый в Северной Америке 12-значный цифровой формат для однозначной идентификации товара в рознице.',
                        'EAN-13 — международный «родственник» UPC, состоит из 13 цифр, первые из которых означают код страны. Этот формат есть практически на всех товарах на полках магазинов по всему миру.',
                        'Code 39 — самопроверяющийся формат, способный кодировать буквы и цифры вместе. Благодаря простоте и надёжности до сих пор широко используется в логистике, автопроме и военных/государственных системах.',
                        'Code 128 — буквенно-цифровой формат высокой плотности, вмещающий больше данных на той же длине штриха. Его вариант GS1-128 — стандарт де-факто во всей цепочке поставок: доставка, склад, транспорт.',
                        'Чтобы выбрать подходящий сканер, сначала нужно понять, с какими форматами вы работаете — большинство современных CCD и лазерных сканеров уверенно читают все четыре формата.',
                    ],
                ],
            ],
            [
                'slug' => '2d-barcode-types',
                'image' => 'images/news/2d-barcode-types.svg',
                'published_at' => '2026-08-17',
                'read_minutes' => 4,
                'uz' => [
                    'title' => '2D kod turlari: QR, Data Matrix va PDF417',
                    'excerpt' => 'Uchta mashhur 2D format — va ularning har biri aslida qanday vazifa uchun yaratilgan.',
                    'body' => [
                        'QR-kod — eng tanish 2D format: kvadrat shakl, uch burchagida katta "topish" kvadratchalari bor. Marketing havolalari, to\'lov tizimlari va chiptalarda keng tarqalgan, chunki uni oddiy telefon kamerasi ham osongina o\'qiydi.',
                        'Data Matrix — juda ixcham kvadrat yoki to\'rtburchak naqsh, hatto bir necha millimetrli o\'lchamda ham o\'qiladigan holatda qoladi. Shu sababli elektronika komponentlari, tibbiy asboblar va dori qadoqlarini markirovka qilishda standart hisoblanadi.',
                        'PDF417 — cho\'ziq, "qatlamlangan" to\'rtburchak format bo\'lib, katta hajmdagi matnni (masalan, shaxsni tasdiqlovchi hujjat ma\'lumotlarini) saqlashga mo\'ljallangan. Haydovchilik guvohnomalari va bordingpasslarda tez-tez uchraydi.',
                        'Uch formatning barchasi xatolarni tuzatish imkoniyatiga ega, ammo har biri o\'z vazifasiga moslashtirilgan: QR — tezkor va universal, Data Matrix — kichik joyga, PDF417 — katta hajmdagi matnga.',
                        'Agar sizga kichik detallarni yoki dori qadoqlarini belgilash kerak bo\'lsa, 2D imager (CMOS) skaner tanlang — u nafaqat QR, balki Data Matrix va PDF417 ni ham bir xil ishonchlilikda o\'qiydi.',
                    ],
                ],
                'ru' => [
                    'title' => 'Типы 2D-кодов: QR, Data Matrix и PDF417',
                    'excerpt' => 'Три популярных 2D-формата — и для какой задачи на самом деле создан каждый из них.',
                    'body' => [
                        'QR-код — самый узнаваемый 2D-формат: квадратная форма с крупными «поисковыми» квадратами в трёх углах. Широко используется в рекламных ссылках, платёжных системах и билетах — его легко считывает даже обычная камера телефона.',
                        'Data Matrix — очень компактный квадратный или прямоугольный узор, остающийся читаемым даже при размере в несколько миллиметров. Поэтому это стандарт для маркировки электронных компонентов, медицинских изделий и упаковок лекарств.',
                        'PDF417 — вытянутый «многослойный» прямоугольный формат, рассчитанный на хранение больших объёмов текста (например, данных удостоверения личности). Часто встречается на водительских правах и посадочных талонах.',
                        'Все три формата умеют исправлять ошибки, но каждый заточен под свою задачу: QR — быстрый и универсальный, Data Matrix — для малых поверхностей, PDF417 — для больших объёмов текста.',
                        'Если нужно маркировать мелкие детали или упаковки лекарств, выбирайте 2D имидж-сканер (CMOS) — он одинаково уверенно читает и QR, и Data Matrix, и PDF417.',
                    ],
                ],
            ],
            [
                'slug' => 'ccd-laser-imager-scan-engines',
                'image' => 'images/news/ccd-laser-imager-scan-engines.svg',
                'published_at' => '2026-08-24',
                'read_minutes' => 5,
                'uz' => [
                    'title' => 'Skaner "ichida" nima bor: lazer, CCD va CMOS obyektivlar',
                    'excerpt' => 'Uchta asosiy skanerlash texnologiyasi qanday ishlashi va bir-biridan nimasi bilan farqlanishi.',
                    'body' => [
                        'Lazer skanerlar harakatlanuvchi lazer nurini kod ustidan o\'tkazadi va aks etgan yorug\'likni fotodiod orqali o\'lchaydi. Bu usul uzoq masofadan 1D kodlarni tez va aniq o\'qishga imkon beradi, lekin faqat chiziqli kodlar bilan ishlaydi.',
                        'CCD (Charge-Coupled Device) skanerlar harakatlanuvchi qismlarga ega emas — ular o\'zining LED yorug\'ligi bilan kodni yoritib, mayda sensorlar qatori orqali "surat" oladi. Bunday skanerlar chidamliroq va uzoq xizmat qiladi, ammo odatda qisqaroq masofada ishlaydi.',
                        'CMOS asosidagi 2D imager (kamera) skanerlar butun maydonni kichik kamera kabi suratga oladi, so\'ng dastur ichidan istalgan shtrix-kod yoki 2D kodni, qanday burchakda bo\'lishidan qat\'iy nazar, taniydi. Aynan shu moslashuvchanlik tufayli bugungi kunda eng ko\'p tarqalgan texnologiyaga aylandi.',
                        'CMOS skanerlarning yana bir muhim afzalligi — ular telefon yoki planshet ekranidan chiqadigan kodlarni ham o\'qiy oladi, bu esa elektron chipta va mobil to\'lovlar uchun juda muhim.',
                        'Qaysi texnologiyani tanlash — sizning kodlaringiz turi (faqat 1D yoki 2D ham), ishlash masofasi va atrof-muhit sharoitiga bog\'liq. Aniq tavsiya kerak bo\'lsa, mahsulot sahifasidagi "Narx so\'rash" tugmasi orqali biz bilan bog\'laning.',
                    ],
                ],
                'ru' => [
                    'title' => 'Что внутри сканера: лазер, CCD и CMOS-объективы',
                    'excerpt' => 'Как устроены три основные технологии сканирования и чем они отличаются друг от друга.',
                    'body' => [
                        'Лазерные сканеры направляют движущийся лазерный луч на код и измеряют отражённый свет фотодиодом. Этот способ позволяет быстро и точно считывать 1D-коды с большого расстояния, но работает только с линейными кодами.',
                        'CCD-сканеры (Charge-Coupled Device) не имеют движущихся частей — они подсвечивают код собственным светодиодом и «фотографируют» его через ряд миниатюрных сенсоров. Такие сканеры более долговечны, но обычно работают на меньшей дистанции.',
                        'Имидж-сканеры (камеры) на базе CMOS снимают всю область как маленькая камера, а затем программно распознают любой штрих-код или 2D-код независимо от угла. Именно эта универсальность сделала их сегодня самой распространённой технологией.',
                        'Ещё одно важное преимущество CMOS-сканеров — они считывают коды прямо с экрана телефона или планшета, что критично для электронных билетов и мобильных платежей.',
                        'Выбор технологии зависит от типа ваших кодов (только 1D или и 2D тоже), дистанции сканирования и условий эксплуатации. Если нужна конкретная рекомендация — напишите нам через кнопку «Запросить цену» на странице модели.',
                    ],
                ],
            ],
            [
                'slug' => 'choosing-scanner-form-factor',
                'image' => 'images/news/choosing-scanner-form-factor.svg',
                'published_at' => '2026-09-01',
                'read_minutes' => 4,
                'uz' => [
                    'title' => 'Qo\'lda, statsionar yoki rugged: qaysi shaklni tanlash kerak?',
                    'excerpt' => 'Skanerning "shakli" ish jarayoningizga qanday mos kelishini aniqlash uchun qisqa yo\'riqnoma.',
                    'body' => [
                        'Qo\'lda ushlab turiladigan skaner — eng universal variant: kassada, kichik omborda yoki turli o\'lchamdagi buyumlar bilan ishlashda qulay, simli yoki simsiz bo\'lishi mumkin.',
                        'Statsionar (mahkamlangan) skaner bir joyga o\'rnatiladi va buyum belgilangan nuqtadan o\'tayotganda avtomatik skanerlaydi — konveyer liniyalari, o\'z-o\'ziga xizmat ko\'rsatish kassalari va metro/chipta darvozalarida qo\'l band bo\'lmasligi uchun ishlatiladi.',
                        'Sanoat (rugged) skanerlar changga va suvga qarshi himoya (masalan, IP65) hamda tushishga chidamli korpusga ega — ular ombor, zavod va ochiq havoda uzoq muddat xizmat qilish uchun mo\'ljallangan.',
                        'PDA (ma\'lumot yig\'uvchi terminal) — skaner, ekran va kichik kompyuterni bitta qurilmada birlashtiradi. Inventarizatsiya, yetkazib berishni tasdiqlash va ombor bo\'ylab harakatlanib ishlash uchun qulay.',
                        'Tanlashda savol oddiy: qurilma bir joyda turadimi yoki xodim u bilan harakatlanadimi, va atrof-muhit qanchalik og\'ir? Javobga qarab to\'g\'ri toifani Winson katalogidan tanlash mumkin.',
                    ],
                ],
                'ru' => [
                    'title' => 'Ручной, стационарный или защищённый: какой форм-фактор выбрать?',
                    'excerpt' => 'Короткое руководство о том, как «форма» сканера должна соответствовать вашему рабочему процессу.',
                    'body' => [
                        'Ручной сканер — самый универсальный вариант: удобен на кассе, в небольшом складе или при работе с товарами разного размера, бывает проводным и беспроводным.',
                        'Стационарный сканер крепится в одном месте и автоматически считывает код, когда товар проходит через заданную точку — используется на конвейерных линиях, кассах самообслуживания и турникетах, чтобы освободить руки.',
                        'Промышленные (защищённые) сканеры имеют защиту от пыли и воды (например, IP65) и ударопрочный корпус — рассчитаны на долгую службу на складе, заводе и под открытым небом.',
                        'ТСД (терминал сбора данных) объединяет сканер, экран и небольшой компьютер в одном устройстве. Удобен для инвентаризации, подтверждения доставки и работы в движении по складу.',
                        'Вопрос при выборе прост: устройство стоит на месте или сотрудник перемещается с ним, и насколько суровы условия эксплуатации? Ответ подскажет нужную категорию в каталоге Winson.',
                    ],
                ],
            ],
            [
                'slug' => 'rfid-vs-barcode',
                'image' => 'images/news/rfid-vs-barcode.svg',
                'published_at' => '2026-09-10',
                'read_minutes' => 5,
                'uz' => [
                    'title' => 'RFID va shtrix-kod: raqib emas, hamkor texnologiyalar',
                    'excerpt' => 'RFID shtrix-kodni "almashtiradimi"? Aslida ikkalasi turli vazifalar uchun mo\'ljallangan.',
                    'body' => [
                        'Shtrix-kodni o\'qish uchun skaner va kod bir-birini "ko\'rishi" kerak — ya\'ni to\'g\'ridan-to\'g\'ri ko\'rinish chizig\'i talab qilinadi va har safar faqat bitta buyum skanerlanadi. Buning o\'rniga, chop etish arzon va standart butun dunyoda bir xil.',
                        'RFID esa radioto\'lqinlar orqali ishlaydi — ko\'rinish chizig\'i shart emas, hatto quti yoki qadoq ichidagi teglarni ham o\'qish mumkin. Eng muhimi, bir vaqtning o\'zida o\'nlab, hatto yuzlab teglarni — masalan, butun bir palletni — bir zumda "o\'qib" chiqish mumkin.',
                        'Ammo bu qulaylikning narxi bor: RFID teglari oddiy chop etilgan shtrix-koddan sezilarli darajada qimmatroq, shuning uchun har bir arzon mahsulotga RFID yopishtirish iqtisodiy jihatdan har doim ham oqlanmaydi.',
                        'Amalda ko\'plab kompaniyalar ikkalasini birga ishlatadi: kundalik hisobot va kassa uchun arzon shtrix-kod, qimmatbaho yoki tez harakatlanadigan aktivlar (kiyim-kechak do\'konlari, konteynerlar, kirish kartalari, yo\'l to\'lovi) uchun esa RFID.',
                        'Qaysi birini tanlash — aniqlik va tezlik qanchalik muhimligiga, shuningdek byudjetga bog\'liq. Winson jamoasi ikkala yo\'nalish bo\'yicha ham maslahat bera oladi.',
                    ],
                ],
                'ru' => [
                    'title' => 'RFID и штрих-код: не соперники, а партнёры',
                    'excerpt' => 'Заменяет ли RFID штрих-код? На самом деле у них разные задачи.',
                    'body' => [
                        'Чтобы считать штрих-код, сканер и код должны «видеть» друг друга — нужна прямая линия видимости, и за раз считывается только один предмет. Зато печать таких кодов дешёвая и одинаковая во всём мире.',
                        'RFID работает через радиоволны — прямая видимость не нужна, можно считать метку даже внутри коробки или упаковки. А главное — можно мгновенно «прочитать» сразу десятки и даже сотни меток, например, целую паллету.',
                        'У этого удобства есть цена: RFID-метки заметно дороже обычного печатного штрих-кода, поэтому клеить их на каждый недорогой товар экономически не всегда оправдано.',
                        'На практике многие компании используют оба варианта вместе: дешёвый штрих-код — для повседневного учёта и кассы, а RFID — для дорогих или быстро перемещающихся активов (магазины одежды, контейнеры, пропуска, дорожные платежи).',
                        'Выбор зависит от того, насколько важны точность и скорость, а также от бюджета. Команда Winson может проконсультировать по обоим направлениям.',
                    ],
                ],
            ],
            [
                'slug' => 'choosing-scanner-for-your-industry',
                'image' => 'images/news/choosing-scanner-for-your-industry.svg',
                'published_at' => '2026-09-18',
                'read_minutes' => 5,
                'uz' => [
                    'title' => 'Sohangiz uchun qanday skaner kerak: qisqa yo\'riqnoma',
                    'excerpt' => 'Chakana savdo, ombor, tibbiyot va ishlab chiqarish uchun amaliy tavsiyalar.',
                    'body' => [
                        'Chakana savdo kassasi uchun: 2D CMOS stol usti yoki qo\'lda skaner tanlang — u nafaqat qog\'ozdagi, balki mijoz telefoni ekranidagi chegirma kuponi yoki mobil to\'lov kodini ham o\'qiy oladi.',
                        'Ombor va logistika uchun: simsiz (Bluetooth/Wi-Fi) rugged qo\'lda skaner yoki PDA kerak — xodim erkin harakatlanishi va qurilma tushib ketsa ham ishlashda davom etishi kerak.',
                        'Tibbiyot va dorixona uchun: ixcham 2D qo\'lda skaner, dezinfeksiyaga chidamli korpus bilan — gigiyena talablari yuqori bo\'lgan joylarda muhim. Bunday skaner dori qadog\'idagi mayda Data Matrix kodlarini ham o\'qishi kerak.',
                        'Ishlab chiqarish liniyasi uchun: statsionar skaner liniyaga o\'rnatiladi va detal kodini qo\'l tegmasdan avtomatik o\'qiydi — bu jarayonni tezlashtiradi va inson xatosini kamaytiradi.',
                        'Ishonchli tanlov qilish uchun avval ishchi muhitni (harakatchanlik, namlik, chang, masofa) va kodlar turini aniqlang — keyin Winson katalogidagi mos toifaga o\'ting yoki to\'g\'ridan-to\'g\'ri bizga so\'rov yuboring.',
                    ],
                ],
                'ru' => [
                    'title' => 'Какой сканер нужен вашей отрасли: краткое руководство',
                    'excerpt' => 'Практические рекомендации для розницы, склада, медицины и производства.',
                    'body' => [
                        'Для кассы розничного магазина: выбирайте настольный или ручной 2D CMOS-сканер — он считает не только код на бумаге, но и купон со скидкой или код мобильной оплаты прямо с экрана телефона клиента.',
                        'Для склада и логистики: нужен беспроводной (Bluetooth/Wi-Fi) защищённый ручной сканер или ТСД — сотрудник должен свободно перемещаться, а устройство — продолжать работать даже после падения.',
                        'Для медицины и аптек: компактный 2D ручной сканер с корпусом, устойчивым к дезинфекции — важно там, где высокие требования к гигиене. Такой сканер должен уверенно читать и мелкие коды Data Matrix на упаковках лекарств.',
                        'Для производственной линии: стационарный сканер встраивается прямо в линию и автоматически считывает код детали без участия рук — это ускоряет процесс и снижает человеческий фактор.',
                        'Чтобы сделать надёжный выбор, сначала определите условия работы (подвижность, влажность, пыль, дистанция) и тип кодов — затем перейдите в нужную категорию каталога Winson или сразу отправьте нам запрос.',
                    ],
                ],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        foreach (static::all() as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }

        return null;
    }

    /** @return array<int, array<string, mixed>> */
    public static function others(string $slug, int $limit = 3): array
    {
        return array_slice(
            array_values(array_filter(static::all(), fn ($a) => $a['slug'] !== $slug)),
            0,
            $limit
        );
    }
}
