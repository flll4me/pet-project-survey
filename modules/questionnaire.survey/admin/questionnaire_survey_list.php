<?php

use Bitrix\Main\Loader;
use Questionnaire\Survey\SurveyTable;
use Questionnaire\Survey\SurveyService;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('questionnaire.survey');

$sTableID = 'tbl_questionnaire_survey';

$oSort = new CAdminSorting(
    $sTableID,
    'ID',
    'desc'
);

$lAdmin = new CAdminList(
    $sTableID,
    $oSort
);

$surveys = SurveyTable::query()
    ->setSelect([
        'ID',
        'TITLE',
        'ACTIVE',
    ])
    ->setOrder([
        'ID' => 'DESC',
    ])
    ->fetchAll();

    if (isset($_GET['delete_survey'])) {
    $surveyId = (int)$_GET['delete_survey'];

    $survey = SurveyTable::getById($surveyId)->fetch();

    if (!$survey) {
        throw new \Exception('Опрос не найден');
    }

    SurveyService::deleteSurvey($surveyId);

    LocalRedirect(
        '/bitrix/admin/questionnaire_survey_list.php?lang=' . LANGUAGE_ID
    );
    }

$lAdmin->AddHeaders([
    [
        'id' => 'ID',
        'content' => 'ID',
        'sort' => 'ID',
        'default' => true,
    ],

    [
        'id' => 'DELETE',
        'content' => 'Действие',
        'default' => true,
    ],

    [
        'id' => 'TITLE',
        'content' => 'Название',
        'sort' => 'TITLE',
        'default' => true,
    ],
    [
        'id' => 'ACTIVE',
        'content' => 'Активен',
        'sort' => 'ACTIVE',
        'default' => true,
    ],
]);

foreach ($surveys as $survey) {
    $row = &$lAdmin->AddRow(
        $survey['ID'],
        $survey
    );

    $row->AddViewField(
        'TITLE',
        '<a href="questionnaire_survey_edit.php?lang=' . LANGUAGE_ID . '&id=' . $survey['ID'] . '">'
        . htmlspecialcharsbx($survey['TITLE'])
        . '</a>'
    );

    $row->AddViewField(
        'ACTIVE',
        $survey['ACTIVE'] ? 'Да' : 'Нет'
    );

    $row->AddViewField(
        'DELETE',
        '<a href="?lang=' . LANGUAGE_ID . '&delete_survey=' . $survey['ID'] . '"
        onclick="return confirm(\'Удалить этот опрос вместе со всеми вопросами и вариантами ответа?\');">
        Удалить
    </a>'
    );

}

$lAdmin->AddFooter([
    [
        'title' => 'Всего',
        'value' => count($surveys),
    ],
]);

$lAdmin->AddAdminContextMenu([
    [
        'TEXT' => 'Добавить опрос',
        'LINK' => 'questionnaire_survey_edit.php?lang=' . LANGUAGE_ID,
        'ICON' => 'btn_new',
    ],
]);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle('Опросы');

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$lAdmin->DisplayList();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';