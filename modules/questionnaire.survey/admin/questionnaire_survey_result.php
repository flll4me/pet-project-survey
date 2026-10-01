<?php

use Bitrix\Main\Loader;
use Questionnaire\Survey\SurveyService;

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('questionnaire.survey');

$responseId = (int)($_GET['id'] ?? 0);

if ($responseId <= 0) {
    throw new \Exception('Результат не указан');
}

$response = SurveyService::getResponse($responseId);

if ($response === null) {
    throw new \Exception('Результат не найден');
}

$APPLICATION->SetTitle('Результат опроса');

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_after.php';
?>

    <div class="adm-detail-content">
        <table class="adm-detail-content-table edit-table">
            <tr>
                <td width="40%">Опрос:</td>
                <td>
                    <?= htmlspecialcharsbx($response['SURVEY_TITLE']) ?>
                </td>
            </tr>

            <tr>
                <td>Пользователь:</td>
                <td>
                    <?= (int)$response['USER_ID'] ?>
                </td>
            </tr>

            <tr>
                <td>Дата прохождения:</td>
                <td>
                    <?= htmlspecialcharsbx((string)$response['CREATED_AT']) ?>
                </td>
            </tr>
        </table>

        <br>

        <h2>Ответы</h2>

        <table class="adm-list-table" style="width: 100%; table-layout: fixed;">
            <thead>
            <tr class="adm-list-table-header">
                <th style="width: 60%; text-align: left;">Вопрос</th>
                <th style="width: 40%; text-align: left;">Ответ</th>
            </tr>
            </thead>

            <tbody>

            <tbody>
            <?php foreach ($response['ANSWERS'] as $answer): ?>
                <tr class="adm-list-table-row">
                    <td style="width: 60%; text-align: left;">
                        <?= htmlspecialcharsbx($answer['QUESTION_TITLE']) ?>
                    </td>

                    <td style="width: 40%; text-align: left;">
                        <?php if ($answer['TEXT_VALUE'] !== null): ?>
                            <?= htmlspecialcharsbx($answer['TEXT_VALUE']) ?>
                        <?php else: ?>
                            <?= htmlspecialcharsbx($answer['ANSWER_TITLE']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/epilog_admin.php';