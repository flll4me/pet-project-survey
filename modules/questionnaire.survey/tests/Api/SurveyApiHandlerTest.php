<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\AnswerOptionTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyApiHandler;
use Questionnaire\Survey\SurveyTable;

final class SurveyApiHandlerTest extends TestCase
{
    public function testCanGetSurveyAsJson(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос через HTTP',
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
        $result = SurveyApiHandler::getSurvey($survey->getId());

        // Assert
        $this->assertJson($result);

        $data = json_decode($result, true);

        $this->assertSame(
            'Опрос через HTTP',
            $data['TITLE']
        );

        $this->assertCount(
            1,
            $data['QUESTIONS']
        );

        $this->assertSame(
            'Какой язык вы знаете?',
            $data['QUESTIONS'][0]['TITLE']
        );
    }
}