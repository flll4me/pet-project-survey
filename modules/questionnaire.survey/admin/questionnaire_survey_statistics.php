<?php

use Bitrix\Main\Loader;
use Questionnaire\Survey\SurveyService;
use Questionnaire\Survey\SurveyTable;

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('questionnaire.survey');

$surveyId = (int)($_GET['survey_id'] ?? 0);

$APPLICATION->SetTitle('Статистика');

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_after.php';

if ($surveyId <= 0) {
    $surveys = SurveyTable::query()
        ->setSelect(['ID', 'TITLE'])
        ->setOrder(['TITLE' => 'ASC'])
        ->fetchAll();
    ?>

    <div style="margin-bottom: 20px;">
        <h2>Выберите опрос</h2>

        <form method="get">
            <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">

            <select name="survey_id">
                <option value="">Выберите опрос</option>

                <?php foreach ($surveys as $survey): ?>
                    <option value="<?= (int)$survey['ID'] ?>">
                        <?= htmlspecialcharsbx($survey['TITLE']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="adm-btn">
                Показать статистику
            </button>
        </form>
    </div>

    <?php
    require_once $_SERVER['DOCUMENT_ROOT']
        . '/bitrix/modules/main/include/epilog_admin.php';

    return;
}

$survey = SurveyTable::getById($surveyId)->fetch();

if (!$survey) {
    CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => 'Опрос не найден.',
    ]);

    require_once $_SERVER['DOCUMENT_ROOT']
        . '/bitrix/modules/main/include/epilog_admin.php';

    return;
}

$statistics = SurveyService::getStatistics($surveyId);

$APPLICATION->SetTitle('Статистика: ' . $survey['TITLE']);
?>

    <div style="margin-bottom: 20px;">
        <h2><?= htmlspecialcharsbx($survey['TITLE']) ?></h2>

        <p>
            Всего прохождений:
            <strong><?= $statistics['TOTAL_RESPONSES'] ?></strong>
        </p>
    </div>

<?php foreach ($statistics['QUESTIONS'] as $question): ?>

    <div style="margin-bottom: 30px;">
        <h3>
            <?= htmlspecialcharsbx($question['TITLE']) ?>
        </h3>

        <?php if ($question['TYPE'] === 'text'): ?>

            <p>
                Отвечено:
                <strong><?= $question['ANSWERED_COUNT'] ?></strong>
            </p>

        <?php else: ?>

            <table class="adm-list-table">
                <thead>
                <tr class="adm-list-table-header">
                    <td class="adm-list-table-cell">
                        Вариант
                    </td>
                    <td class="adm-list-table-cell">
                        Количество
                    </td>
                    <td class="adm-list-table-cell">
                        Процент
                    </td>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($question['OPTIONS'] as $option): ?>
                    <tr class="adm-list-table-row">
                        <td class="adm-list-table-cell">
                            <?= htmlspecialcharsbx($option['TITLE']) ?>
                        </td>

                        <td class="adm-list-table-cell">
                            <?= $option['COUNT'] ?>
                        </td>

                        <td class="adm-list-table-cell">
                            <?= $option['PERCENT'] ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>

<?php endforeach; ?>

<?php

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/epilog_admin.php';