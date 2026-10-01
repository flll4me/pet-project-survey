<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\AnswerOptionTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyApi;
use Questionnaire\Survey\SurveyTable;

final class SurveyApiTest extends TestCase
{
    public function testCanGetSurvey(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос через API',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какой язык вы знаете?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'PHP',
            'SORT' => 100,
        ]);

        // Act
        $result = SurveyApi::getSurvey($survey->getId());

        // Assert
        $this->assertSame(
            'Опрос через API',
            $result['TITLE']
        );

        $this->assertCount(1, $result['QUESTIONS']);
        $this->assertSame(
            'Какой язык вы знаете?',
            $result['QUESTIONS'][0]['TITLE']
        );

        $this->assertCount(
            1,
            $result['QUESTIONS'][0]['OPTIONS']
        );
    }
}