<?php

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

class questionnaire_survey extends CModule
{
    public function __construct()
    {
        $this->MODULE_ID = 'questionnaire.survey';
        $this->MODULE_NAME = 'Опросник';
        $this->MODULE_DESCRIPTION = 'Модуль для работы с опросниками';
        $this->MODULE_VERSION = '1.0.0';
        $this->MODULE_VERSION_DATE = '2026-09-22';
    }

    public function DoInstall(): void
    {
        $connection = Application::getConnection();

        if (!$connection->isTableExists('questionnaire_survey')) {
            $connection->createTable(
                'questionnaire_survey',
                [
                    'ID' => new IntegerField('ID'),
                    'TITLE' => new StringField('TITLE'),
                    'ACTIVE' => new BooleanField('ACTIVE'),
                ],
                ['ID'],
                ['ID']
            );
        }

        if (!$connection->isTableExists('questionnaire_question')) {
            $connection->createTable(
                'questionnaire_question',
                [
                    'ID' => new IntegerField('ID'),
                    'SURVEY_ID' => new IntegerField('SURVEY_ID'),
                    'TITLE' => new StringField('TITLE'),
                    'TYPE' => new StringField('TYPE'),
                    'SORT' => new IntegerField('SORT'),
                    'REQUIRED' => new BooleanField('REQUIRED'),
                ],
                ['ID'],
                ['ID']
            );
        }

        if (!$connection->isTableExists('questionnaire_answer_option')) {
            $connection->createTable(
                'questionnaire_answer_option',
                [
                    'ID' => new IntegerField('ID'),
                    'QUESTION_ID' => new IntegerField('QUESTION_ID'),
                    'TITLE' => new StringField('TITLE'),
                    'SORT' => new IntegerField('SORT'),
                ],
                ['ID'],
                ['ID']
            );
        }

        if (!$connection->isTableExists('questionnaire_survey_response')) {
            $connection->createTable(
                'questionnaire_survey_response',
                [
                    'ID' => new IntegerField('ID'),
                    'SURVEY_ID' => new IntegerField('SURVEY_ID'),
                    'USER_ID' => new IntegerField('USER_ID'),
                    'CREATED_AT' => new \Bitrix\Main\ORM\Fields\DatetimeField('CREATED_AT'),
                ],
                ['ID'],
                ['ID']
            );
        }

        if (!$connection->isTableExists('questionnaire_user_answer')) {
            $connection->createTable(
                'questionnaire_user_answer',
                [
                    'ID' => new IntegerField('ID'),
                    'RESPONSE_ID' => new IntegerField('RESPONSE_ID'),
                    'QUESTION_ID' => new IntegerField('QUESTION_ID'),
                    'ANSWER_OPTION_ID' => new IntegerField('ANSWER_OPTION_ID', [
                        'nullable' => true,
                    ]),
                    'TEXT_VALUE' => new StringField('TEXT_VALUE', [
                        'nullable' => true,
                    ]),
                ],
                ['ID'],
                ['ID']
            );
        }

        RegisterModule($this->MODULE_ID);
    }

    public function DoUninstall(): void
    {
        UnRegisterModule($this->MODULE_ID);
    }
}