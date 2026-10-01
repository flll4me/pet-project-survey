<?php

use Bitrix\Main\Loader;
use Questionnaire\Survey\SurveyService;
use Questionnaire\Survey\SurveyTable;

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('questionnaire.survey');

$surveyId = (int)($_GET['survey_id'] ?? 0);

$userId = (int)($_GET['user_id'] ?? 0);
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

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

if ($surveyId <= 0) {
    die('Не указан ID опроса.');
}

$survey = SurveyTable::getById($surveyId)->fetch();

if (!$survey) {
    die('Опрос не найден.');
}

$rows = SurveyService::getResultsForExport(
    $surveyId > 0 ? $surveyId : null,
    $userId > 0 ? $userId : null,
    $dateFromObject,
    $dateToObject
);

$csv = SurveyService::generateCsv($rows);

$filename = 'survey_' . $surveyId . '_results.csv';

header('Content-Type: text/csv; charset=UTF-8');
header(
    'Content-Disposition: attachment; filename="' . $filename . '"'
);
header('Content-Length: ' . strlen($csv));

echo "\xEF\xBB\xBF";
echo $csv;

exit;