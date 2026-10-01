<?php

namespace Questionnaire\Survey;

use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class QuestionTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'questionnaire_question';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new IntegerField('SURVEY_ID'),

            new StringField('TITLE'),

            new StringField('TYPE'),

            new IntegerField('SORT'),

            new BooleanField('REQUIRED'),

            new Reference(
                'SURVEY',
                SurveyTable::class,
                Join::on('this.SURVEY_ID', 'ref.ID')
            ),

            new OneToMany(
                'OPTIONS',
                AnswerOptionTable::class,
                'QUESTION'
            ),

        ];
    }
}