<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\AnswerOptionTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyEndpoint;
use Questionnaire\Survey\SurveyTable;

final class SurveyEndpointTest extends TestCase
{
    public function testCanGetSurveyById(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос endpoint',
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
        $result = SurveyEndpoint::getSurvey($survey->getId());

        // Assert
        $this->assertJson($result);

        $data = json_decode($result, true);

        $this->assertSame(
            'Опрос endpoint',
            $data['TITLE']
        );

        $this->assertCount(
            1,
            $data['QUESTIONS']
        );
    }
}