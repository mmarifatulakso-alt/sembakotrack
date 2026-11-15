<?php

return [
    'api_url'     => env('SEMBAKO_API_URL', 'https://api-panelhargav2.badanpangan.go.id/api/front/harga-pangan-informasi'),
    'sumber'      => env('SEMBAKO_SOURCE_NAME', 'Panel Harga Bapanas'),
    'timeout'     => 20,
    'days_back'   => (int) env('SEMBAKO_DAYS_BACK', 365),
    'date_format' => env('SEMBAKO_API_DATE_FORMAT', 'd-m-Y'),

    'headers' => [
        'User-Agent' => 'SembakoTrack/1.0 (tugas kuliah Cyber Security)',
    ],

    'query' => [
        'province_id'    => '',
        'city_id'        => '',
        'level_harga_id' => '3',
    ],

    'response' => [
        'data_path' => 'data',
        'date_key'  => 'yesterday_date',
        'price_key' => 'today',
    ],

    // 27 Komoditas Lengkap Panel Harga Bapanas (Tingkat Konsumen)
    'komoditas' => [
        // Kelompok Beras & Biji-bijian
        ['kode' => 'beras-premium',           'nama' => 'Beras Premium',                          'satuan' => 'kg',    'kategori' => 'Beras',   'api_id' => 27],
        ['kode' => 'beras-medium',            'nama' => 'Beras Medium',                           'satuan' => 'kg',    'kategori' => 'Beras',   'api_id' => 28],
        ['kode' => 'beras-sphp',              'nama' => 'Beras SPHP',                             'satuan' => 'kg',    'kategori' => 'Beras',   'api_id' => 109],
        ['kode' => 'beras-medium-non-sphp',   'nama' => 'Beras Medium Non SPHP',                  'satuan' => 'kg',    'kategori' => 'Beras',   'api_id' => 165],
        ['kode' => 'beras-khusus-lokal',      'nama' => 'Beras Khusus (Lokal)',                   'satuan' => 'kg',    'kategori' => 'Beras',   'api_id' => 166],
        ['kode' => 'jagung-peternak',         'nama' => 'Jagung Tk Peternak',                     'satuan' => 'kg',    'kategori' => 'Pokok',   'api_id' => 102],
        ['kode' => 'kedelai-impor',           'nama' => 'Kedelai Biji Kering (Impor)',            'satuan' => 'kg',    'kategori' => 'Pokok',   'api_id' => 29],

        // Kelompok Bawang & Cabai
        ['kode' => 'bawang-merah',            'nama' => 'Bawang Merah',                           'satuan' => 'kg',    'kategori' => 'Bawang',  'api_id' => 30],
        ['kode' => 'bawang-putih',            'nama' => 'Bawang Putih Bonggol',                   'satuan' => 'kg',    'kategori' => 'Bawang',  'api_id' => 31],
        ['kode' => 'cabai-merah-keriting',    'nama' => 'Cabai Merah Keriting',                   'satuan' => 'kg',    'kategori' => 'Cabai',   'api_id' => 32],
        ['kode' => 'cabai-merah-besar',       'nama' => 'Cabai Merah Besar',                      'satuan' => 'kg',    'kategori' => 'Cabai',   'api_id' => 136],
        ['kode' => 'cabai-rawit-merah',       'nama' => 'Cabai Rawit Merah',                      'satuan' => 'kg',    'kategori' => 'Cabai',   'api_id' => 33],

        // Kelompok Protein (Daging, Telur, Ikan)
        ['kode' => 'daging-sapi-murni',       'nama' => 'Daging Sapi Murni',                      'satuan' => 'kg',    'kategori' => 'Protein', 'api_id' => 34],
        ['kode' => 'daging-ayam-ras',         'nama' => 'Daging Ayam Ras',                        'satuan' => 'kg',    'kategori' => 'Protein', 'api_id' => 35],
        ['kode' => 'telur-ayam-ras',          'nama' => 'Telur Ayam Ras',                         'satuan' => 'kg',    'kategori' => 'Protein', 'api_id' => 36],
        ['kode' => 'ikan-kembung',            'nama' => 'Ikan Kembung',                           'satuan' => 'kg',    'kategori' => 'Protein', 'api_id' => 104],
        ['kode' => 'ikan-tongkol',            'nama' => 'Ikan Tongkol',                           'satuan' => 'kg',    'kategori' => 'Protein', 'api_id' => 105],
        ['kode' => 'ikan-bandeng',            'nama' => 'Ikan Bandeng',                           'satuan' => 'kg',    'kategori' => 'Protein', 'api_id' => 106],
        ['kode' => 'daging-kerbau-beku',      'nama' => 'Daging Kerbau Beku (Impor Luar Negeri)', 'satuan' => 'kg',    'kategori' => 'Protein', 'api_id' => 108],
        ['kode' => 'daging-kerbau-segar',     'nama' => 'Daging Kerbau Segar (Lokal)',            'satuan' => 'kg',    'kategori' => 'Protein', 'api_id' => 152],

        // Kelompok Bahan Pokok Lainnya (Gula, Minyak, Tepung, Garam)
        ['kode' => 'gula-konsumsi',           'nama' => 'Gula Konsumsi',                          'satuan' => 'kg',    'kategori' => 'Pokok',   'api_id' => 37],
        ['kode' => 'minyak-goreng-kemasan',   'nama' => 'Minyak Goreng Kemasan',                  'satuan' => 'liter', 'kategori' => 'Pokok',   'api_id' => 38],
        ['kode' => 'minyak-goreng-curah',     'nama' => 'Minyak Goreng Curah',                    'satuan' => 'liter', 'kategori' => 'Pokok',   'api_id' => 101],
        ['kode' => 'minyakita',               'nama' => 'Minyakita',                              'satuan' => 'liter', 'kategori' => 'Pokok',   'api_id' => 135],
        ['kode' => 'tepung-terigu-curah',     'nama' => 'Tepung Terigu (Curah)',                  'satuan' => 'kg',    'kategori' => 'Pokok',   'api_id' => 40],
        ['kode' => 'tepung-terigu-kemasan',   'nama' => 'Tepung Terigu Kemasan',                  'satuan' => 'kg',    'kategori' => 'Pokok',   'api_id' => 103],
        ['kode' => 'garam-konsumsi',          'nama' => 'Garam Konsumsi',                         'satuan' => 'kg',    'kategori' => 'Pokok',   'api_id' => 107],
    ],

    'admin' => [
        'name'     => env('ADMIN_NAME', 'Admin'),
        'email'    => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];