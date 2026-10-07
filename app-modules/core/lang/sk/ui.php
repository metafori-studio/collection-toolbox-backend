<?php

return [
    'navigation_groups' => [
        'system' => 'Systém',
    ],
    'resources' => [
        'location' => [
            'label' => 'Lokalita',
            'plural_label' => 'Lokality',
            'navigation_label' => 'Lokality',
        ],
        'keyword' => [
            'label' => 'Kľúčové slovo',
            'plural_label' => 'Kľúčové slová',
            'navigation_label' => 'Kľúčové slová',
        ],
        'organization' => [
            'label' => 'Organizácia',
            'plural_label' => 'Organizácie',
            'navigation_label' => 'Organizácie',
        ],
        'person' => [
            'label' => 'Osoba',
            'plural_label' => 'Osoby',
            'navigation_label' => 'Osoby',
        ],
        'user' => [
            'label' => 'Používateľ',
            'plural_label' => 'Používatelia',
            'navigation_label' => 'Používatelia',
        ],
    ],
    'fields' => [
        'name' => 'Názov',
        'user_name' => 'Meno',
        'email' => 'E-mail',
        'preferred_locale' => 'Preferovaný jazyk',
        'roles' => 'Roly',
        'precision' => 'Presnosť',
        'start_precision' => 'Presnosť začiatku',
        'end_precision' => 'Presnosť konca',
        'date_range' => 'Časové rozpätie',
        'start' => 'Začiatok',
        'date' => 'Dátum',
        'end' => 'Koniec',
        'country' => 'Štát',
        'region' => 'Kraj',
        'district' => 'Okres',
        'municipality' => 'Obec',
        'parent' => 'Nadradená lokalita',
        'latitude' => 'Zemepisná šírka',
        'longitude' => 'Zemepisná dĺžka',
        'given_name' => 'Krstné meno',
        'family_name' => 'Priezvisko',
        'orcid' => 'ORCID',
        'ror_id' => 'ROR ID',
    ],
    'placeholders' => [
        'none' => 'Žiadna',
    ],
    'validation' => [
        'end_date_must_be_after_start_date' => 'Koniec musí byť neskôr ako začiatok.',
    ],
];
