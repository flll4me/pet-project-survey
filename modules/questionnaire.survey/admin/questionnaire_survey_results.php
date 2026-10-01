<?php

use Bitrix\Main\Loader;
use Questionnaire\Survey\SurveyService;
use Questionnaire\Survey\SurveyTable;

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('questionnaire.survey');

$sTableID = 'tbl_questionnaire_survey_results';

$oSort = new CAdminSorting(
    $sTableID,
    'ID',
    'desc'
);

$lAdmin = new CAdminList(
    $sTableID,
    $oSort
);

$surveyId = (int)($_GET['survey_id'] ?? 0);
$userId = (int)($_GET['user_id'] ?? 0);
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';


$surveys = SurveyTable::query()
    ->setSelect(['ID', 'TITLE'])
    ->setOrder(['TITLE' => 'ASC'])
    ->fetchAll();

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

$responses = SurveyService::getResponses(
    $surveyId > 0 ? $surveyId : null,
    $userId > 0 ? $userId : null,
    $dateFromObject,
    $dateToObject
);

$lAdmin->AddHeaders([
    [
        'id' => 'ID',
        'content' => 'ID',
        'sort' => 'ID',
        'default' => true,
    ],
    [
        'id' => 'SURVEY_TITLE',
        'content' => 'Опрос',
        'default' => true,
    ],
    [
        'id' => 'USER_ID',
        'content' => 'Пользователь',
        'default' => true,
    ],
    [
        'id' => 'CREATED_AT',
        'content' => 'Дата прохождения',
        'sort' => 'CREATED_AT',
        'default' => true,
    ],

    [
        'id' => 'STATISTICS',
        'content' => 'Статистика',
        'default' => true,
    ],

]);

foreach ($responses as $response) {
    $row = &$lAdmin->AddRow(
        $response['ID'],
        $response
    );

    $row->AddViewField(
        'ID',
        '<a href="questionnaire_survey_result.php?lang=' . LANGUAGE_ID . '&id=' . (int)$response['ID'] . '">'
        . (int)$response['ID']
        . '</a>'
    );

    $row->AddViewField(
        'SURVEY_TITLE',
        htmlspecialcharsbx($response['SURVEY_TITLE'])
    );

    $row->AddViewField(
        'USER_ID',
        $response['USER_ID']
    );

    $row->AddViewField(
        'CREATED_AT',
        $response['CREATED_AT']
    );

    $row->AddViewField(
        'STATISTICS',
        '<a href="questionnaire_survey_statistics.php?lang=' . LANGUAGE_ID
        . '&survey_id=' . (int)$response['SURVEY_ID'] . '">'
        . 'Открыть'
        . '</a>'
    );

}

$lAdmin->AddFooter([
    [
        'title' => 'Всего',
        'value' => count($responses),
    ],
]);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle('Результаты опросов');

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_after.php';
?>

    <form method="get" style="margin-bottom:15px;">
        <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">

        <select name="survey_id">
            <option value="0">Все опросы</option>

            <?php foreach ($surveys as $survey): ?>
                <option
                        value="<?= (int)$survey['ID'] ?>"
                    <?= $surveyId === (int)$survey['ID'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialcharsbx($survey['TITLE']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input
                type="number"
                name="user_id"
                value="<?= $userId > 0 ? $userId : '' ?>"
                placeholder="ID пользователя"
                min="1"
        >

        <label>
            Дата от:
            <input
                    type="date"
                    name="date_from"
                    value="<?= htmlspecialcharsbx($dateFrom) ?>"
            >
        </label>

        <label>
            Дата до:
            <input
                    type="date"
                    name="date_to"
                    value="<?= htmlspecialcharsbx($dateTo) ?>"
            >
        </label>

        <button type="submit" class="adm-btn">Найти</button>

        <?php if ($surveyId > 0): ?>
            <a
                    href="questionnaire_survey_export.php?lang=<?= LANGUAGE_ID ?>&survey_id=<?= $surveyId ?>&user_id=<?= $userId ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>"
                    class="adm-btn"
            >
                Экспорт CSV
            </a>

            <a
                    href="questionnaire_survey_export_excel.php?lang=<?= LANGUAGE_ID ?>&survey_id=<?= $surveyId ?>&user_id=<?= $userId ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>"
                    class="adm-btn"
            >
                Экспорт Excel
            </a>
        <?php endif; ?>

        <?php if ($surveyId > 0 || $userId > 0 || $dateFrom !== '' || $dateTo !== ''): ?>
            <a href="questionnaire_survey_results.php?lang=<?= LANGUAGE_ID ?>">
                Сбросить
            </a>
        <?php endif; ?>
    </form>

<?php

$lAdmin->DisplayList();

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/epilog_admin.php';