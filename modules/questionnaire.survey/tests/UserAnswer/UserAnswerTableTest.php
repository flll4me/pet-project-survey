<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\AnswerOptionTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyResponseTable;
use Questionnaire\Survey\SurveyTable;
use Questionnaire\Survey\UserAnswerTable;

final class UserAnswerTableTest extends TestCase
{
    public function testCanCreateAndGetUserAnswer(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос для UserAnswer',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какой язык вам нравится?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $option = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'JavaScript',
            'SORT' => 100,
        ]);

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 1,
        ]);

        // Act
        $result = UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $option->getId(),
        ]);

        $answerId = $result->getId();

        $answer = UserAnswerTable::getById($answerId)->fetch();

        // Assert
        $this->assertNotNull($answer);
        $this->assertEquals($response->getId(), $answer['RESPONSE_ID']);
        $this->assertEquals($question->getId(), $answer['QUESTION_ID']);
        $this->assertEquals($option->getId(), $answer['ANSWER_OPTION_ID']);
    }

    public function testResponseCanGetUserAnswers(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос с ответами',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какой язык?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $option = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'PHP',
            'SORT' => 100,
        ]);

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 1,
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $option->getId(),
        ]);

        // Act
        $responseObject = SurveyResponseTable::getById(
            $response->getId()
        )->fetchObject();

        $responseObject->fill(['USER_ANSWERS']);

        $answers = $responseObject->getUserAnswers();

        // Assert
        $this->assertNotNull($answers);
        $this->assertCount(1, $answers);
    }

    public function testCanCreateTextAnswer(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос с текстом',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Расскажите о себе',
            'TYPE' => 'text',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 1,
        ]);

        $text = 'Я изучаю PHP и Bitrix';

        // Act
        $result = UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'TEXT_VALUE' => $text,
        ]);

        $answer = UserAnswerTable::getById(
            $result->getId()
        )->fetch();

        // Assert
        $this->assertNotNull($answer);
        $this->assertEquals($question->getId(), $answer['QUESTION_ID']);
        $this->assertSame($text, $answer['TEXT_VALUE']);
        $this->assertNull($answer['ANSWER_OPTION_ID']);
    }

    public function testCanCreateMultipleAnswersForOneQuestion(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос с multiple',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какие языки вы знаете?',
            'TYPE' => 'multiple',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $phpOption = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'PHP',
            'SORT' => 100,
        ]);

        $jsOption = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'JavaScript',
            'SORT' => 200,
        ]);

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 1,
        ]);

        // Act
        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $phpOption->getId(),
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $jsOption->getId(),
        ]);

        $answers = UserAnswerTable::query()
            ->setSelect([
                'ID',
                'ANSWER_OPTION_ID',
            ])
            ->setFilter([
                'RESPONSE_ID' => $response->getId(),
                'QUESTION_ID' => $question->getId(),
            ])
            ->setOrder([
                'ID' => 'ASC',
            ])
            ->fetchAll();

        // Assert
        $this->assertCount(2, $answers);

        $this->assertEquals(
            $phpOption->getId(),
            $answers[0]['ANSWER_OPTION_ID']
        );

        $this->assertEquals(
            $jsOption->getId(),
            $answers[1]['ANSWER_OPTION_ID']
        );
    }
}