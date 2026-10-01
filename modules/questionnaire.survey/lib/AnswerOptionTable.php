<?php

namespace Questionnaire\Survey;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class AnswerOptionTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'questionnaire_answer_option';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new IntegerField('QUESTION_ID'),

            new StringField('TITLE'),

            new IntegerField('SORT'),

            new Reference(
                'QUESTION',
                QuestionTable::class,
                Join::on('this.QUESTION_ID', 'ref.ID')
            ),
        ];
    }
}