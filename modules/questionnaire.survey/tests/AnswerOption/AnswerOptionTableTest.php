<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\AnswerOptionTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyTable;

final class AnswerOptionTableTest extends TestCase
{
    public function testCanCreateAndGetAnswerOption(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос для вариантов',
            'ACTIVE' => true,
        ]);

        $surveyId = $survey->getId();

        $question = QuestionTable::add([
            'SURVEY_ID' => $surveyId,
            'TITLE' => 'Какой язык вам нравится?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $questionId = $question->getId();

        $title = 'JavaScript';

        // Act
        $result = AnswerOptionTable::add([
            'QUESTION_ID' => $questionId,
            'TITLE' => $title,
            'SORT' => 100,
        ]);

        $optionId = $result->getId();

        $option = AnswerOptionTable::getById($optionId)->fetch();

        // Assert
        $this->assertNotNull($option);
        $this->assertSame($title, $option['TITLE']);
        $this->assertEquals($questionId, $option['QUESTION_ID']);
    }

    public function testAnswerOptionCanGetQuestion(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос для связи',
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
            'TITLE' => 'JavaScript',
            'SORT' => 100,
        ]);

        // Act
        $optionObject = AnswerOptionTable::getById(
            $option->getId()
        )->fetchObject();

        $optionObject->fill(['QUESTION']);

        $relatedQuestion = $optionObject->getQuestion();

        // Assert
        $this->assertNotNull($relatedQuestion);
        $this->assertSame('Какой язык?', $relatedQuestion->getTitle());
    }

    public function testQuestionCanGetOptions(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос с вариантами',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какой язык?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'JavaScript',
            'SORT' => 100,
        ]);

        AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'PHP',
            'SORT' => 200,
        ]);

        // Act
        $questionObject = QuestionTable::getById(
            $question->getId()
        )->fetchObject();

        $questionObject->fill(['OPTIONS']);

        $options = $questionObject->getOptions();

        // Assert
        $this->assertNotNull($options);
        $this->assertCount(2, $options);
    }

    public function testCanCreateAnswerOptionFromFormData(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос из формы',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какой язык программирования вы знаете?',
            'TYPE' => 'multiple',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $title = 'JavaScript';

        $result = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => $title,
            'SORT' => 100,
        ]);

        $this->assertTrue($result->isSuccess());

        $option = AnswerOptionTable::getById($result->getId())->fetch();

        $this->assertNotFalse($option);
        $this->assertSame($title, $option['TITLE']);
        $this->assertEquals($question->getId(), (int)$option['QUESTION_ID']);
    }

}