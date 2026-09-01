<?php
// Карта страниц сайта для раздела «Страницы» админки.
// Каждая страница: адрес, ключ SEO (data/seo.json) и её тексты, разбитые по блокам.
// Ключи — из lang/{ru,az,en}.php; правки сохраняются в data/texts.json.
return [
    'home' => [
        'label'  => 'Главная',
        'url'    => '/',
        'seo'    => 'home',
        'groups' => [
            'Первый экран'        => ['hero_eyebrow', 'hero_h', 'hero_lead', 'hero_cta2', 'hero_trust', 'hero_badge_t', 'hero_badge_d'],
            'Слайды первого экрана' => ['hs2_t', 'hs2_d', 'hs3_t', 'hs3_d', 'hs4_t', 'hs4_d'],
            'Почему мы'           => ['u1t', 'u1d', 'u2t', 'u2d', 'u3t', 'u3d', 'u4t', 'u4d'],
            'Поводы'              => ['occ_t', 'occ_d', 'occ1', 'occ2', 'occ3', 'occ4', 'occ5', 'occ6', 'occ7', 'occ8'],
            'Блоки каталога'      => ['sec_bento_t', 'sec_bento_d', 'btn_all_bento', 'bantik_d', 'sets_d', 'sec_ctg_t', 'sec_ctg_d', 'btn_all_ctg'],
            'Размеры и цены'      => ['sizes_t', 'sizes_d', 'sizes_bento_h', 'size_b1_w', 'size_b1_p', 'size_b1_c', 'size_b2_w', 'size_b2_p', 'size_b2_c', 'size_b3_w', 'size_b3_p', 'size_b3_c', 'size_bk_w', 'size_bk_p', 'size_bk_c', 'size_st_w', 'size_st_p', 'size_st_c', 'sizes_ctg_h', 'size_c1_w', 'size_c1_c', 'size_c2_w', 'size_c2_c', 'sizes_ctg_note'],
            'Начинки'             => ['fl_t', 'fl_d', 'fl_choose'],
            'Один чат — один торт' => ['chat_tag', 'chat_t', 'chat_d', 'chat_online', 'cb1', 'cb2', 'cb3', 'cb4', 'chat_s1t', 'chat_s1d', 'chat_s2t', 'chat_s2d', 'chat_s3t', 'chat_s3d', 'chat_btn'],
            'Блок «О нас»'        => ['about_eyebrow', 'about_t', 'about_p1', 'about_p2', 'stat1', 'stat2', 'stat3', 'about_btn'],
            'Instagram'           => ['ig_d', 'ig_btn'],
        ],
    ],
    'bento' => [
        'label'  => 'Бенто-торты',
        'url'    => '/bolme/bento-tort/',
        'seo'    => 'bento',
        'groups' => [
            'Шапка страницы'  => ['bento_h', 'bento_d'],
            'Блоки категорий' => ['bantik_d', 'sets_d'],
            'Размеры и цены'  => ['sizes_bento_h', 'sizes_d', 'size_b1_w', 'size_b1_p', 'size_b1_c', 'size_b2_w', 'size_b2_p', 'size_b2_c', 'size_b3_w', 'size_b3_p', 'size_b3_c', 'size_bk_w', 'size_bk_p', 'size_bk_c', 'size_st_w', 'size_st_p', 'size_st_c'],
            'Начинки'         => ['fl_t', 'fl_d', 'fl1_t', 'fl1_s', 'fl2_t', 'fl2_s', 'fl3_t', 'fl3_s'],
            'Блок в конце'    => ['bento_more_t', 'bento_more_d'],
        ],
    ],
    'ctg' => [
        'label'  => 'Cake to go',
        'url'    => '/bolme/cake-to-go/',
        'seo'    => 'ctg',
        'groups' => [
            'Шапка страницы' => ['ctg_h', 'ctg_d'],
            'Размеры и цены' => ['ctg_sizes_h', 'size_c1_w', 'size_c1_c', 'size_c2_w', 'size_c2_c', 'sizes_ctg_note'],
            'Начинки'        => ['fl_t', 'fl_d', 'fl1_t', 'fl1_s', 'fl2_t', 'fl2_s', 'fl3_t', 'fl3_s'],
        ],
    ],
    'fillings' => [
        'label'  => 'Начинки',
        'url'    => '/terkibler/',
        'seo'    => 'fillings',
        'groups' => [
            'Шапка страницы' => ['fil_h', 'fil_d'],
            'Бисквиты'       => ['fl1_t', 'fl1_s', 'fld1', 'fl2_t', 'fl2_s', 'fld2', 'fl3_t', 'fl3_s', 'fld3'],
            'Списки начинок' => ['fl1_items', 'fl2_items', 'fl3_items'],
            'Подсказки'      => ['fl_choose', 'fl_d', 'fil_note_t', 'fil_note_d', 'ig_btn'],
        ],
    ],
    'reviews' => [
        'label'  => 'Отзывы',
        'url'    => '/reyler/',
        'seo'    => 'reviews',
        'groups' => [
            'Шапка страницы' => ['rev_h', 'rev_d', 'rev_count'],
            'Блок в конце'   => ['rev_cta_t', 'rev_cta_d', 'rev_empty'],
        ],
    ],
    'konstruktor' => [
        'label'  => 'Конструктор торта',
        'url'    => '/konstruktor/',
        'seo'    => 'konstruktor',
        'groups' => [
            'Шапка страницы'   => ['k_h', 'k_d', 'k_hint'],
            'Панель настроек'  => ['k_cream', 'k_img', 'k_img_own', 'k_img_none', 'k_img_size', 'k_text', 'k_add_text', 'k_text_color', 'k_sel_none', 'k_sel_size', 'k_del'],
            'Сообщения'        => ['k_limit_img', 'k_limit_text', 'k_custom_name', 'k_cream_lbl', 'k_text_n', 'k_wa_intro'],
        ],
    ],
    'about' => [
        'label'  => 'О нас',
        'url'    => '/haqqimizda/',
        'seo'    => 'about',
        'groups' => [
            'Шапка страницы' => ['about_h', 'about_eyebrow', 'about_t'],
            'Текст о нас'    => ['about_full_p1', 'about_full_p2', 'about_full_p3'],
            'Цифры'          => ['stat1', 'stat2', 'stat3'],
            'Ценности'       => ['about_v1t', 'about_v1d', 'about_v2t', 'about_v2d', 'about_v3t', 'about_v3d'],
        ],
    ],
    'faq' => [
        'label'  => 'FAQ',
        'url'    => '/faq/',
        'seo'    => 'faq',
        'groups' => [
            'Шапка страницы'  => ['faq_h', 'faq_d'],
            'Вопросы и ответы' => ['f1q', 'f1a', 'f2q', 'f2a', 'f7q', 'f7a', 'f3q', 'f3a', 'f4q', 'f4a', 'f5q', 'f5a', 'f6q', 'f6a'],
        ],
    ],
    'contact' => [
        'label'  => 'Контакты',
        'url'    => '/elaqe/',
        'seo'    => 'contact',
        'groups' => [
            'Шапка страницы' => ['contact_h', 'contact_d'],
            'Способы связи'  => ['contact_wa', 'contact_wa_d', 'contact_phone', 'contact_ig', 'contact_ig_d', 'contact_email'],
            'Режим и доставка' => ['contact_hours', 'hours_full', 'delivery_t', 'delivery_d'],
            'Адрес и карта'  => ['contact_addr', 'contact_addr_v', 'contact_map', 'contact_route'],
            'Блок в конце'   => ['contact_note_t', 'contact_note_d'],
        ],
    ],
    'product' => [
        'label'  => 'Карточка товара',
        'url'    => '/mehsul/…/',
        'seo'    => null,          // у каждого торта свой SEO — в разделе «Товары»
        'groups' => [
            'Размеры в форме заказа' => ['sizes_opt_bento', 'sizes_opt_bantik', 'sizes_opt_set', 'sizes_opt_ctg'],
            'Описание под фото'      => ['pd_w_bento', 'pd_w_bantik', 'pd_w_set', 'pd_w_ctg'],
            'Вкладки'                => ['tab_desc', 'pd_desc', 'tab_fill', 'tab_time', 'pd_time', 'tab_del', 'pd_del', 'related_h'],
            'Своя картинка'          => ['up_t', 'up_d', 'up_hint', 'up_loading', 'up_ok', 'up_ok_d', 'up_remove'],
            'Кнопка и надпись'       => ['pd_order', 'f_text', 'f_text_ph'],
            'Если торт не найден'    => ['nf_text'],
        ],
    ],
    'common' => [
        'label'  => 'Общие блоки',
        'url'    => null,
        'seo'    => null,
        'groups' => [
            'Меню'          => ['nav_home', 'nav_bento', 'nav_ctg', 'nav_fillings', 'nav_reviews', 'nav_konstr', 'nav_about', 'nav_faq', 'nav_contact', 'breadcrumb_home'],
            'Шапка'         => ['preorder_note', 'hours', 'btn_wa', 'btn_wa_short'],
            'Подвал'        => ['footer_desc', 'footer_menu', 'footer_pages', 'footer_contact', 'footer_rights', 'bantik_h', 'sets_h'],
            'Кнопка Instagram' => ['ig_fab_t', 'ig_fab_d'],
            'Названия товаров' => ['p_bento', 'p_bantik', 'p_set', 'pcs'],
            'Сообщения в WhatsApp' => ['wa_msg', 'wa_msg_p', 'wa_link_lbl', 'wa_design', 'wa_photo', 'wa_point'],
            'Форма заказа'  => ['ord_t', 'opt_size', 'opt_sponge', 'opt_fill', 'opt_date', 'opt_time', 'opt_dl', 'dl_courier', 'dl_bolt', 'dl_pickup', 'dl_bolt_note', 'dl_bolt_addr', 'date_ph', 'date_note', 'time_ph', 'f_address', 'f_address_ph', 'f_name', 'f_name_ph', 'f_phone', 'f_phone_ph', 'f_other', 'f_recipient', 'f_rname_ph', 'f_rphone_ph', 'req_mark', 'ord_send', 'ord_confirm', 'pd_note'],
            'Выбор точки на карте' => ['map_btn', 'map_t', 'map_hint', 'map_locate', 'map_apply', 'map_searching'],
            'Ошибки и проверки' => ['val_fill', 'val_phone', 'up_e_type', 'up_e_size', 'up_e_rate', 'up_e_generic'],
        ],
    ],
];
