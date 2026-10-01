<?php

use Bitrix\Main\Loader;
use Questionnaire\Survey\SurveyService;
use Questionnaire\Survey\SurveyTable;

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('questionnaire.survey');
require_once $_SERVER['DOCUMENT_ROOT']
    . '/local/modules/questionnaire.survey/vendor/autoload.php';

$surveyId = (int)($_GET['survey_id'] ?? 0);
$userId = (int)($_GET['user_id'] ?? 0);
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

if ($surveyId <= 0) {
    die('Не указан ID опроса.');
}

$survey = SurveyTable::getById($surveyId)->fetch();

if (!$survey) {
    die('Опрос не найден.');
}

$dateFromObject = null;
$dateToObject = null;

if ($dateFrom !== '') {
    $dateFromObject = new \Bitrix\Main\Type\DateTime(
        date('d.m.Y', strtotime($dateFrom)) . ' 00:00:00'
    );
}

if ($dateTo !== '') {
    $dateToObject = new \Bitrix\Main\Type\DateTime(
        date('d.m.Y', strtotime($dateTo)) . ' 23:59:59'
    );
}

$rows = SurveyService::getResultsForExport(
    $surveyId,
    $userId > 0 ? $userId : null,
    $dateFromObject,
    $dateToObject
);

$excel = SurveyService::generateExcel($rows);

$filename = 'survey_' . $surveyId . '_results.xlsx';

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' . $filename . '"'
);

header('Content-Length: ' . strlen($excel));

echo $excel;

exit;