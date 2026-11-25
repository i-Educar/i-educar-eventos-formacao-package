<?php

return [
    'public' => [
        'aparecer_na_home' => (bool) env('EVENTOS_APARECER_NA_HOME', false),
        'titulo' => 'Eventos e formação',
        'ordem' => 58,
        'url' => '/eventos-formacao/portal',
    ],

    'frequencia_minima_certificado' => (float) env('EVENTOS_FREQ_MIN_CERT', 75.0),
];
