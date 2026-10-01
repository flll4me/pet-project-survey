<?php

namespace Questionnaire\Survey;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

class SurveyTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'questionnaire_survey';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new StringField('TITLE'),

            new BooleanField('ACTIVE'),

            new OneToMany(
                'QUESTIONS',
                QuestionTable::class,
                'SURVEY'
            ),
        ];
    }
}