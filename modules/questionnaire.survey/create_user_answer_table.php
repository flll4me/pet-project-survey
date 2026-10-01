<?php

$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require __DIR__ . '/vendor/autoload.php';

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

$connection = Application::getConnection();

if (!$connection->isTableExists('questionnaire_user_answer')) {
    $connection->createTable(
        'questionnaire_user_answer',
        [
            'ID' => new IntegerField('ID'),
            'RESPONSE_ID' => new IntegerField('RESPONSE_ID'),
            'QUESTION_ID' => new IntegerField('QUESTION_ID'),
            'ANSWER_OPTION_ID' => new IntegerField('ANSWER_OPTION_ID'),
            'TEXT_VALUE' => new StringField('TEXT_VALUE'),
        ],
        ['ID'],
        ['ID']
    );

    echo "UserAnswer table created\n";
} else {
    echo "UserAnswer table already exists\n";
}