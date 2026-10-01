<?php

use Bitrix\Main\Loader;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyTable;
use Questionnaire\Survey\AnswerOptionTable;

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('questionnaire.survey');

$surveyId = (int)($_GET['survey_id'] ?? 0);
$questionId = (int)($_GET['id'] ?? 0);

if ($surveyId <= 0) {
    throw new \Exception('Опрос не указан');
}

$survey = SurveyTable::getById($surveyId)->fetch();

if (!$survey) {
    throw new \Exception('Опрос не найден');
}

$question = null;
$options = [];

if ($questionId > 0) {
    $question = QuestionTable::getById($questionId)->fetch();

    if (!$question) {
        throw new \Exception('Вопрос не найден');
    }

    if ((int)$question['SURVEY_ID'] !== $surveyId) {
        throw new \Exception('Вопрос не принадлежит этому опросу');
    }

    $options = AnswerOptionTable::query()
        ->setSelect(['ID', 'TITLE', 'SORT'])
        ->where('QUESTION_ID', $questionId)
        ->setOrder(['SORT' => 'ASC'])
        ->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['TITLE'] ?? '');
    $type = $_POST['TYPE'] ?? '';
    $required = isset($_POST['REQUIRED']);

    if ($title === '') {
        throw new \Exception('Название вопроса не может быть пустым');
    }

    if ($questionId > 0) {
        $result = QuestionTable::update($questionId, [
            'TITLE' => $title,
            'TYPE' => $type,
            'REQUIRED' => $required,
        ]);
    } else {
        $result = QuestionTable::add([
            'SURVEY_ID' => $surveyId,
            'TITLE' => $title,
            'TYPE' => $type,
            'SORT' => 100,
            'REQUIRED' => $required,
        ]);

        if ($result->isSuccess()) {
            $questionId = $result->getId();
        }
    }

    if (!$result->isSuccess()) {
        throw new \Exception(
            implode(', ', $result->getErrorMessages())
        );
    }

    /*
     * При редактировании удаляем старые варианты
     * и создаём заново те, которые пришли из формы.
     */
    if ($questionId > 0) {
        $oldOptions = AnswerOptionTable::query()
            ->setSelect(['ID'])
            ->where('QUESTION_ID', $questionId)
            ->fetchAll();

        foreach ($oldOptions as $oldOption) {
            AnswerOptionTable::delete((int)$oldOption['ID']);
        }
    }

    $optionsFromForm = $_POST['OPTIONS'] ?? [];

    if ($type !== 'text') {
        foreach ($optionsFromForm as $sort => $optionTitle) {
            $optionTitle = trim($optionTitle);

            if ($optionTitle === '') {
                continue;
            }

            AnswerOptionTable::add([
                'QUESTION_ID' => $questionId,
                'TITLE' => $optionTitle,
                'SORT' => ($sort + 1) * 100,
            ]);
        }
    }

    LocalRedirect(
        '/bitrix/admin/questionnaire_survey_edit.php?lang='
        . LANGUAGE_ID
        . '&id='
        . $surveyId
    );
}

$isEdit = $questionId > 0;

$APPLICATION->SetTitle(
    $isEdit ? 'Редактирование вопроса' : 'Новый вопрос'
);

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_admin_after.php';

?>

    <h1>
        <?= $isEdit ? 'Редактирование вопроса' : 'Новый вопрос' ?>
    </h1>

    <p>
        Опрос:
        <?= htmlspecialcharsbx($survey['TITLE']) ?>
    </p>

    <form method="post">

        <div style="margin-top: 20px;">

            <label>
                Вопрос:
                <input
                        type="text"
                        name="TITLE"
                        size="60"
                        value="<?= htmlspecialcharsbx($question['TITLE'] ?? '') ?>"
                >
            </label>

        </div>

        <div style="margin-top: 15px;">

            <label>
                Тип вопроса:
                <select name="TYPE">

                    <option
                            value="single"
                        <?= (($question['TYPE'] ?? 'single') === 'single') ? 'selected' : '' ?>
                    >
                        Один вариант
                    </option>

                    <option
                            value="multiple"
                        <?= (($question['TYPE'] ?? '') === 'multiple') ? 'selected' : '' ?>
                    >
                        Несколько вариантов
                    </option>

                    <option
                            value="text"
                        <?= (($question['TYPE'] ?? '') === 'text') ? 'selected' : '' ?>
                    >
                        Текстовый ответ
                    </option>

                </select>
            </label>

        </div>

        <div style="margin-top: 15px;">

            <label>
                <input
                        type="checkbox"
                        name="REQUIRED"
                        value="1"
                    <?= !empty($question['REQUIRED']) ? 'checked' : '' ?>
                >
                Обязательный вопрос
            </label>

        </div>

        <div
                id="options-block"
                style="margin-top: 15px;"
        >

            <div>
                <strong>Варианты ответа:</strong>
            </div>

            <div id="options-list">

                <?php if (!empty($options)): ?>

                    <?php foreach ($options as $option): ?>

                        <div
                                class="option-row"
                                style="margin-top: 10px;"
                        >

                            <input
                                    type="text"
                                    name="OPTIONS[]"
                                    size="40"
                                    placeholder="Вариант ответа"
                                    value="<?= htmlspecialcharsbx($option['TITLE']) ?>"
                            >

                            <button
                                    type="button"
                                    class="remove-option"
                            >
                                Удалить
                            </button>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div
                            class="option-row"
                            style="margin-top: 10px;"
                    >

                        <input
                                type="text"
                                name="OPTIONS[]"
                                size="40"
                                placeholder="Вариант ответа"
                        >

                        <button
                                type="button"
                                class="remove-option"
                        >
                            Удалить
                        </button>

                    </div>

                <?php endif; ?>

            </div>

            <div style="margin-top: 10px;">

                <button
                        type="button"
                        id="add-option"
                >
                    + Добавить вариант
                </button>

            </div>

        </div>

        <div style="margin-top: 15px;">

            <button
                    type="submit"
                    name="save"
            >
                Сохранить
            </button>

        </div>

    </form>

    <script>

        const typeSelect = document.querySelector('select[name="TYPE"]');
        const optionsBlock = document.getElementById('options-block');
        const optionsList = document.getElementById('options-list');
        const addOptionButton = document.getElementById('add-option');

        function updateOptionsVisibility() {

            if (typeSelect.value === 'text') {
                optionsBlock.style.display = 'none';
            } else {
                optionsBlock.style.display = 'block';
            }

        }

        typeSelect.addEventListener('change', updateOptionsVisibility);

        addOptionButton.addEventListener('click', () => {

            const row = document.createElement('div');

            row.className = 'option-row';
            row.style.marginTop = '10px';

            row.innerHTML = `
            <input
                type="text"
                name="OPTIONS[]"
                size="40"
                placeholder="Вариант ответа"
            >

            <button
                type="button"
                class="remove-option"
            >
                Удалить
            </button>
        `;

            optionsList.appendChild(row);

        });

        optionsList.addEventListener('click', (event) => {

            if (event.target.classList.contains('remove-option')) {

                const rows = optionsList.querySelectorAll('.option-row');

                if (rows.length > 1) {
                    event.target.closest('.option-row').remove();
                }

            }

        });

        updateOptionsVisibility();

    </script>

<?php

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/epilog_admin.php';