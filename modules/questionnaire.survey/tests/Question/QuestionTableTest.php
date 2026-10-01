<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyTable;

final class QuestionTableTest extends TestCase
{
    public function testCanCreateAndGetQuestion(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос для вопроса',
            'ACTIVE' => true,
        ]);

        $surveyId = $survey->getId();

        $title = 'Какой у вас возраст?';

        // Act
        $result = QuestionTable::add([
            'SURVEY_ID' => $surveyId,
            'TITLE' => $title,
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $questionId = $result->getId();

        $question = QuestionTable::getById($questionId)->fetch();

        // Assert
        $this->assertNotNull($question);
        $this->assertSame($title, $question['TITLE']);
        $this->assertEquals($surveyId, $question['SURVEY_ID']);
    }

    public function testSurveyCanGetQuestions(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос со связанными вопросами',
            'ACTIVE' => true,
        ]);

        $surveyId = $survey->getId();

        QuestionTable::add([
            'SURVEY_ID' => $surveyId,
            'TITLE' => 'Первый вопрос',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        QuestionTable::add([
            'SURVEY_ID' => $surveyId,
            'TITLE' => 'Второй вопрос',
            'TYPE' => 'text',
            'SORT' => 200,
            'REQUIRED' => false,
        ]);

        // Act
        $surveyObject = SurveyTable::getById($surveyId)->fetchObject();

        $surveyObject->fill(['QUESTIONS']);

        $questions = $surveyObject->getQuestions();

        // Assert
        $this->assertNotNull($questions);
        $this->assertCount(2, $questions);
    }

    public function testCanUpdateQuestion(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос для изменения вопроса',
            'ACTIVE' => true,
        ]);

        $surveyId = $survey->getId();

        $question = QuestionTable::add([
            'SURVEY_ID' => $surveyId,
            'TITLE' => 'Старый вопрос',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $questionId = $question->getId();

        $result = QuestionTable::update($questionId, [
            'TITLE' => 'Новый вопрос',
            'REQUIRED' => false,
        ]);

        $this->assertTrue($result->isSuccess());

        $updatedQuestion = QuestionTable::getById($questionId)->fetch();

        $this->assertNotFalse($updatedQuestion);
        $this->assertSame('Новый вопрос', $updatedQuestion['TITLE']);
        $this->assertFalse((bool)$updatedQuestion['REQUIRED']);
    }

    public function testCanCreateQuestionFromFormData(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос из формы',
            'ACTIVE' => true,
        ]);

        $surveyId = $survey->getId();

        $result = QuestionTable::add([
            'SURVEY_ID' => $surveyId,
            'TITLE' => 'Какой язык программирования вы знаете?',
            'TYPE' => 'multiple',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $this->assertTrue($result->isSuccess());

        $questionId = $result->getId();

        $question = QuestionTable::getById($questionId)->fetch();

        $this->assertNotFalse($question);
        $this->assertSame(
            'Какой язык программирования вы знаете?',
            $question['TITLE']
        );
        $this->assertSame('multiple', $question['TYPE']);
        $this->assertSame($surveyId, (int)$question['SURVEY_ID']);
        $this->assertTrue((bool)$question['REQUIRED']);
    }

    public function testCanGetQuestionById(): void
    {
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос для редактирования',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Старый вопрос',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $foundQuestion = QuestionTable::getById(
            $question->getId()
        )->fetch();

        $this->assertNotFalse($foundQuestion);
        $this->assertSame('Старый вопрос', $foundQuestion['TITLE']);
        $this->assertSame('single', $foundQuestion['TYPE']);
    }

}