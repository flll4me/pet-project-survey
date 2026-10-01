<?php

return [
    'parent_menu' => 'global_menu_services',
    'sort' => 100,
    'text' => 'Опросник',
    'title' => 'Управление опросниками',
    'items_id' => 'questionnaire_survey',

    'items' => [
        [
            'text' => 'Опросы',
            'url' => 'questionnaire_survey_list.php?lang=' . LANGUAGE_ID,
            'more_url' => [
                'questionnaire_survey_list.php',
                'questionnaire_survey_edit.php',
            ],
        ],
        [
            'text' => 'Результаты',
            'url' => 'questionnaire_survey_results.php?lang=' . LANGUAGE_ID,
        ],
        [
            'text' => 'Статистика',
            'url' => 'questionnaire_survey_statistics.php?lang=' . LANGUAGE_ID,
        ],
    ],
];