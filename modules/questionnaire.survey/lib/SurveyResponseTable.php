<?php

namespace Questionnaire\Survey;

use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;

class SurveyResponseTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'questionnaire_survey_response';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new IntegerField('SURVEY_ID'),

            new IntegerField('USER_ID'),

            new Reference(
                'SURVEY',
                SurveyTable::class,
                Join::on('this.SURVEY_ID', 'ref.ID')
            ),

            new DatetimeField('CREATED_AT', [
                'default_value' => new \Bitrix\Main\Type\DateTime(),
            ]),

            new OneToMany(
                'USER_ANSWERS',
                UserAnswerTable::class,
                'RESPONSE'
            ),
        ];
    }
}