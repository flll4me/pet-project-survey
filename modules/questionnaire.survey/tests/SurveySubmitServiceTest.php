<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\SurveyTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\AnswerOptionTable;
use Questionnaire\Survey\SurveyResponseTable;
use Questionnaire\Survey\UserAnswerTable;
use Questionnaire\Survey\SurveySubmitService;

final class SurveySubmitServiceTest extends TestCase
{
    public function testSubmitSingleAnswer(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Тест отправки',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какой язык вы знаете?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $option = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'PHP',
            'SORT' => 100,
        ]);

        $responseId = SurveySubmitService::submit(
            $survey->getId(),
            123,
            [
                $question->getId() => $option->getId(),
            ]
        );

        $this->assertIsInt($responseId);

        $response = SurveyResponseTable::getById($responseId)->fetch();

        $this->assertNotFalse($response);
        $this->assertSame($survey->getId(), (int)$response['SURVEY_ID']);
        $this->assertSame(123, (int)$response['USER_ID']);

        $answer = UserAnswerTable::query()
            ->setSelect([
                'ID',
                'RESPONSE_ID',
                'QUESTION_ID',
                'ANSWER_OPTION_ID',
                'TEXT_VALUE',
            ])
            ->setFilter([
                '=RESPONSE_ID' => $responseId,
                '=QUESTION_ID' => $question->getId(),
            ])
            ->fetch();

        $this->assertNotFalse($answer);
        $this->assertSame($option->getId(), (int)$answer['ANSWER_OPTION_ID']);
    }

    public function testSubmitTextAnswer(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Тест текстового ответа',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Расскажите о себе',
            'TYPE' => 'text',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $responseId = SurveySubmitService::submit(
            $survey->getId(),
            123,
            [
                $question->getId() => 'Я изучаю PHP',
            ]
        );

        $this->assertIsInt($responseId);

        $answer = UserAnswerTable::query()
            ->setSelect([
                'ID',
                'RESPONSE_ID',
                'QUESTION_ID',
                'ANSWER_OPTION_ID',
                'TEXT_VALUE',
            ])
            ->setFilter([
                '=RESPONSE_ID' => $responseId,
                '=QUESTION_ID' => $question->getId(),
            ])
            ->fetch();

        $this->assertNotFalse($answer);
        $this->assertSame(
            'Я изучаю PHP',
            $answer['TEXT_VALUE']
        );
        $this->assertNull($answer['ANSWER_OPTION_ID']);
    }

    public function testSubmitMultipleAnswers(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Тест множественного ответа',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какие языки вы знаете?',
            'TYPE' => 'multiple',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $optionPhp = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'PHP',
            'SORT' => 100,
        ]);

        $optionJs = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'JavaScript',
            'SORT' => 200,
        ]);

        $responseId = SurveySubmitService::submit(
            $survey->getId(),
            123,
            [
                $question->getId() => [
                    $optionPhp->getId(),
                    $optionJs->getId(),
                ],
            ]
        );

        $this->assertIsInt($responseId);

        $answers = UserAnswerTable::query()
            ->setSelect([
                'ID',
                'QUESTION_ID',
                'ANSWER_OPTION_ID',
            ])
            ->setFilter([
                '=RESPONSE_ID' => $responseId,
                '=QUESTION_ID' => $question->getId(),
            ])
            ->setOrder([
                'ID' => 'ASC',
            ])
            ->fetchAll();

        $this->assertCount(2, $answers);
        $this->assertSame(
            $optionPhp->getId(),
            (int)$answers[0]['ANSWER_OPTION_ID']
        );
        $this->assertSame(
            $optionJs->getId(),
            (int)$answers[1]['ANSWER_OPTION_ID']
        );
    }

    public function testRequiredQuestionMustHaveAnswer(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос с обязательным вопросом',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Обязательный вопрос',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $this->expectException(\Exception::class);

        SurveySubmitService::submit(
            $survey->getId(),
            0,
            []
        );
    }

    public function testOptionalQuestionCanBeSkipped(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос с необязательным вопросом',
            'ACTIVE' => true,
        ]);

        QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Необязательный вопрос',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => false,
        ]);

        $responseId = SurveySubmitService::submit(
            $survey->getId(),
            0,
            []
        );

        $this->assertIsInt($responseId);
        $this->assertGreaterThan(0, $responseId);
    }

    public function testCannotSubmitOptionFromAnotherQuestion(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Тест чужого варианта',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Первый вопрос',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $anotherQuestion = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Второй вопрос',
            'TYPE' => 'single',
            'SORT' => 200,
            'REQUIRED' => false,
        ]);

        $foreignOption = AnswerOptionTable::add([
            'QUESTION_ID' => $anotherQuestion->getId(),
            'TITLE' => 'Чужой вариант',
            'SORT' => 100,
        ]);

        $this->expectException(\Exception::class);

        SurveySubmitService::submit(
            $survey->getId(),
            0,
            [
                $question->getId() => $foreignOption->getId(),
            ]
        );
    }

    public function testCannotSubmitMultipleOptionFromAnotherQuestion(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Тест чужого варианта multiple',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какие языки вы знаете?',
            'TYPE' => 'multiple',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $anotherQuestion = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Другой вопрос',
            'TYPE' => 'single',
            'SORT' => 200,
            'REQUIRED' => false,
        ]);

        $optionPhp = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'PHP',
            'SORT' => 100,
        ]);

        $foreignOption = AnswerOptionTable::add([
            'QUESTION_ID' => $anotherQuestion->getId(),
            'TITLE' => 'Python',
            'SORT' => 100,
        ]);

        $this->expectException(\Exception::class);

        SurveySubmitService::submit(
            $survey->getId(),
            0,
            [
                $question->getId() => [
                    $optionPhp->getId(),
                    $foreignOption->getId(),
                ],
            ]
        );
    }

    public function testCannotSubmitUnknownQuestion(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Тест неизвестного вопроса',
            'ACTIVE' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Question not found');

        SurveySubmitService::submit(
            $survey->getId(),
            0,
            [
                999999 => 123,
            ]
        );
    }

    public function testRequiredSingleQuestionCannotHaveEmptyAnswer(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Тест пустого обязательного ответа',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Выберите язык',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required question has no answer');

        SurveySubmitService::submit(
            $survey->getId(),
            0,
            [
                $question->getId() => '',
            ]
        );
    }

}