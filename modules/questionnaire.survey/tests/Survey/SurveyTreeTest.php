<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\AnswerOptionTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyTable;

final class SurveyTreeTest extends TestCase
{
    public function testSurveyCanGetQuestionsWithOptions(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос о программировании',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какие языки вы знаете?',
            'TYPE' => 'multiple',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'PHP',
            'SORT' => 100,
        ]);

        AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'JavaScript',
            'SORT' => 200,
        ]);

        // Act
        $surveyObject = SurveyTable::getById(
            $survey->getId()
        )->fetchObject();

        $surveyObject->fill(['QUESTIONS']);

        $questions = $surveyObject->getQuestions();

        $questionObject = null;

        foreach ($questions as $question) {
            $questionObject = $question;
            break;
        }

        $questionObject->fill(['OPTIONS']);

        $options = $questionObject->getOptions();

        // Assert
        $this->assertCount(1, $questions);
        $this->assertSame(
            'Какие языки вы знаете?',
            $questionObject->getTitle()
        );

        $this->assertCount(2, $options);

        $optionTitles = [];

        foreach ($options as $option) {
            $optionTitles[] = $option->getTitle();
        }

        $this->assertSame(['PHP', 'JavaScript'], $optionTitles);
    }
}