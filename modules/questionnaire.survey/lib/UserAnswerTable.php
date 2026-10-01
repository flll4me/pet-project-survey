<?php

namespace Questionnaire\Survey;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class UserAnswerTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'questionnaire_user_answer';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new IntegerField('RESPONSE_ID'),

            new IntegerField('QUESTION_ID'),

            new IntegerField('ANSWER_OPTION_ID', [
                'required' => false,
            ]),

            new StringField('TEXT_VALUE', [
                'required' => false,
            ]),

            new Reference(
                'RESPONSE',
                SurveyResponseTable::class,
                Join::on('this.RESPONSE_ID', 'ref.ID')
            ),

            new Reference(
                'QUESTION',
                QuestionTable::class,
                Join::on('this.QUESTION_ID', 'ref.ID')
            ),

            new Reference(
                'ANSWER_OPTION',
                AnswerOptionTable::class,
                Join::on('this.ANSWER_OPTION_ID', 'ref.ID')
            ),

        ];
    }
}