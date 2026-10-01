<?php

use Bitrix\Main\Loader;
use Questionnaire\Survey\SurveyTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\AnswerOptionTable;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('questionnaire.survey');

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$surveyId = (int)$request->getQuery('id');

$survey = null;

if ($surveyId > 0) {
    $survey = SurveyTable::getById($surveyId)->fetch();

    if (!$survey) {
        throw new \Exception('Опрос не найден');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {

    $questionId = (int)$_POST['delete_question'];

    $question = QuestionTable::getById($questionId)->fetch();

    if (!$question) {
        throw new \Exception('Вопрос не найден');
    }

    if ((int)$question['SURVEY_ID'] !== $surveyId) {
        throw new \Exception('Вопрос не принадлежит этому опросу');
    }

    $options = AnswerOptionTable::query()
        ->setSelect(['ID'])
        ->where('QUESTION_ID', $questionId)
        ->fetchAll();

    foreach ($options as $option) {
        AnswerOptionTable::delete((int)$option['ID']);
    }

    $result = QuestionTable::delete($questionId);

    if (!$result->isSuccess()) {
        throw new \Exception(
            implode(', ', $result->getErrorMessages())
        );
    }

    LocalRedirect(
        '/bitrix/admin/questionnaire_survey_edit.php?lang='
        . LANGUAGE_ID
        . '&id='
        . $surveyId
    );
}

$questions = [];

if ($surveyId > 0) {
    $surveyObject = SurveyTable::getById($surveyId)->fetchObject();

    $surveyObject->fill(['QUESTIONS']);

    foreach ($surveyObject->getQuestions() as $question) {
        $questions[] = $question;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['TITLE'] ?? '');
    $active = isset($_POST['ACTIVE']);

    if ($title === '') {
        throw new \Exception('Название опроса не может быть пустым');
    }

    if ($surveyId > 0) {
        $result = SurveyTable::update($surveyId, [
            'TITLE' => $title,
            'ACTIVE' => $active,
        ]);
    } else {
        $result = SurveyTable::add([
            'TITLE' => $title,
            'ACTIVE' => $active,
        ]);
    }

    if ($result->isSuccess()) {
        LocalRedirect(
            '/bitrix/admin/questionnaire_survey_list.php?lang=' . LANGUAGE_ID
        );
    }

    throw new \Exception(
        implode(', ', $result->getErrorMessages())
    );
}

$APPLICATION->SetTitle(
    $surveyId > 0 ? 'Редактирование опроса' : 'Новый опрос'
);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aTabs = [
    [
        'DIV' => 'edit1',
        'TAB' => 'Опрос',
        'TITLE' => 'Создание и редактирование опроса',
    ],
];

$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>

    <form method="post">

        <?php
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>

        <tr>
            <td width="40%">Название:</td>
            <td width="60%">
                <input
                        type="text"
                        name="TITLE"
                        value="<?= htmlspecialcharsbx($survey['TITLE'] ?? '') ?>"
                        size="50"
                >
            </td>
        </tr>

        <tr>
            <td>Активен:</td>
            <td>
                <input
                        type="checkbox"
                        name="ACTIVE"
                        value="1"
                    <?= !empty($survey['ACTIVE']) ? 'checked' : '' ?>
                >
            </td>
        </tr>

        <?php
        $tabControl->End();
        ?>

        <input
                type="submit"
                name="save"
                value="Сохранить"
                class="adm-btn-save"
        >

    </form>

    <h2>Вопросы</h2>

<?php if ($surveyId > 0): ?>

    <a
            href="questionnaire_question_edit.php?lang=<?= LANGUAGE_ID ?>&survey_id=<?= $surveyId ?>"
            class="adm-btn"
    >
        Добавить вопрос
    </a>

<?php else: ?>

    <p>Сначала сохраните опрос, чтобы добавить вопросы.</p>

<?php endif; ?>

<?php if (empty($questions)): ?>

    <p>Вопросов пока нет.</p>

<?php else: ?>

    <table class="adm-list-table">

        <tr class="adm-list-table-header">
            <td class="adm-list-table-cell">ID</td>
            <td class="adm-list-table-cell">Вопрос</td>
            <td class="adm-list-table-cell">Тип</td>
            <td class="adm-list-table-cell">Обязательный</td>
        </tr>

        <?php foreach ($questions as $question): ?>

            <tr>

                <td class="adm-list-table-cell">
                    <?= $question->getId() ?>
                </td>

                <td class="adm-list-table-cell">

                    <a
                            href="questionnaire_question_edit.php?lang=<?= LANGUAGE_ID ?>&survey_id=<?= $surveyId ?>&id=<?= $question->getId() ?>"
                    >
                        <?= htmlspecialcharsbx($question->getTitle()) ?>
                    </a>

                    <form
                            method="post"
                            style="display: inline; margin-left: 10px;"
                    >

                        <input
                                type="hidden"
                                name="delete_question"
                                value="<?= $question->getId() ?>"
                        >

                        <button
                                type="submit"
                                onclick="return confirm('Удалить этот вопрос?');"
                        >
                            Удалить
                        </button>

                    </form>

                    <?php
                    $question->fill(['OPTIONS']);
                    $options = $question->getOptions();
                    ?>

                    <?php if ($question->getType() !== 'text' && $options->count() > 0): ?>

                        <div style="margin-top: 8px; margin-left: 15px;">

                            <?php foreach ($options as $option): ?>

                                <div>
                                    └ <?= htmlspecialcharsbx($option->getTitle()) ?>
                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </td>

                <td class="adm-list-table-cell">
                    <?= htmlspecialcharsbx($question->getType()) ?>
                </td>

                <td class="adm-list-table-cell">
                    <?= $question->getRequired() ? 'Да' : 'Нет' ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

<?php endif; ?>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';