<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\AnswerOptionTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\SurveyResponseTable;
use Questionnaire\Survey\UserAnswerTable;
use Questionnaire\Survey\SurveyService;
use Questionnaire\Survey\SurveyTable;

final class SurveyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        $surveys = SurveyTable::query()
            ->setSelect(['ID'])
            ->whereLike('TITLE', 'TEST_%')
            ->fetchAll();

        foreach ($surveys as $survey) {
            SurveyService::deleteSurvey((int)$survey['ID']);
        }

        parent::tearDown();
    }

    public function testCanGetSurveyWithQuestionsAndOptions(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос о программировании',
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
        $result = SurveyService::getSurvey($survey->getId());

        // Assert
        $this->assertSame(
            'TEST_Опрос о программировании',
            $result['TITLE']
        );

        $this->assertCount(1, $result['QUESTIONS']);

        $this->assertSame(
            'Какие языки вы знаете?',
            $result['QUESTIONS'][0]['TITLE']
        );

        $this->assertCount(2, $result['QUESTIONS'][0]['OPTIONS']);
    }

    public function testReturnsEmptyArrayForNotFoundSurvey(): void
    {
        // Act
        $result = SurveyService::getSurvey(999999);

        // Assert
        $this->assertSame([], $result);
    }

    public function testCanDeleteSurveyWithQuestionsOptionsAndResponses(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для удаления',
            'ACTIVE' => true,
        ]);

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какой язык знаете?',
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

        $userAnswer = UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $option->getId(),
        ]);

        // Act
        SurveyService::deleteSurvey($survey->getId());

        // Assert
        $this->assertFalse(
            SurveyTable::getById($survey->getId())->fetch()
        );

        $this->assertFalse(
            QuestionTable::getById($question->getId())->fetch()
        );

        $this->assertFalse(
            AnswerOptionTable::getById($option->getId())->fetch()
        );

        $this->assertFalse(
            SurveyResponseTable::getById($response->getId())->fetch()
        );

        $this->assertFalse(
            UserAnswerTable::getById($userAnswer->getId())->fetch()
        );
    }

    public function testCanGetSurveyResponses(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для результатов',
            'ACTIVE' => true,
        ]);

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 5,
        ]);

        // Act
        $result = SurveyService::getResponses();

        // Assert
        $found = null;

        foreach ($result as $item) {
            if ((int)$item['ID'] === $response->getId()) {
                $found = $item;
                break;
            }
        }

        $this->assertNotNull($found);

        $this->assertEquals(
            $survey->getId(),
            $found['SURVEY_ID']
        );

        $this->assertEquals(
            'TEST_Опрос для результатов',
            $found['SURVEY_TITLE']
        );

        $this->assertEquals(
            5,
            $found['USER_ID']
        );
    }

    public function testResponsesAreSortedByNewestFirst(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для сортировки',
            'ACTIVE' => true,
        ]);

        $firstResponse = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 1,
        ]);

        $secondResponse = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 2,
        ]);

        // Act
        $result = SurveyService::getResponses();

        // Assert
        $this->assertGreaterThan(
            $firstResponse->getId(),
            $secondResponse->getId()
        );

        $this->assertEquals(
            $secondResponse->getId(),
            $result[0]['ID']
        );
    }

    public function testResponsesContainDataForAdminList(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для админки',
            'ACTIVE' => true,
        ]);

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 10,
        ]);

        // Act
        $result = SurveyService::getResponses();

        // Assert
        $found = null;

        foreach ($result as $item) {
            if ((int)$item['ID'] === $response->getId()) {
                $found = $item;
                break;
            }
        }

        $this->assertNotNull($found);

        $this->assertArrayHasKey('ID', $found);
        $this->assertArrayHasKey('SURVEY_ID', $found);
        $this->assertArrayHasKey('SURVEY_TITLE', $found);
        $this->assertArrayHasKey('USER_ID', $found);
        $this->assertArrayHasKey('CREATED_AT', $found);

        $this->assertEquals(
            'TEST_Опрос для админки',
            $found['SURVEY_TITLE']
        );

        $this->assertEquals(
            10,
            $found['USER_ID']
        );
    }

    public function testCanGetSingleResponseWithAnswers(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для просмотра результата',
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

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 10,
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $option->getId(),
        ]);

        // Act
        $result = SurveyService::getResponse($response->getId());

        // Assert
        $this->assertNotNull($result);

        $this->assertEquals(
            $response->getId(),
            $result['ID']
        );

        $this->assertEquals(
            'TEST_Опрос для просмотра результата',
            $result['SURVEY_TITLE']
        );

        $this->assertEquals(
            10,
            $result['USER_ID']
        );

        $this->assertCount(1, $result['ANSWERS']);

        $this->assertEquals(
            'Какой язык вы знаете?',
            $result['ANSWERS'][0]['QUESTION_TITLE']
        );

        $this->assertEquals(
            'PHP',
            $result['ANSWERS'][0]['ANSWER_TITLE']
        );
    }

    public function testReturnsNullForNotFoundResponse(): void
    {
        // Act
        $result = SurveyService::getResponse(999999);

        // Assert
        $this->assertNull($result);
    }

    public function testCanGetMultipleAnswers(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос multiple',
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

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 10,
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $optionPhp->getId(),
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $optionJs->getId(),
        ]);

        // Act
        $result = SurveyService::getResponse($response->getId());

        // Assert
        $this->assertNotNull($result);

        $this->assertCount(2, $result['ANSWERS']);

        $answerTitles = array_column(
            $result['ANSWERS'],
            'ANSWER_TITLE'
        );

        $this->assertContains('PHP', $answerTitles);
        $this->assertContains('JavaScript', $answerTitles);
    }

    public function testCanGetTextAnswer(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос с текстовым ответом',
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
            'USER_ID' => 10,
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'TEXT_VALUE' => 'Я PHP-разработчик',
        ]);

        // Act
        $result = SurveyService::getResponse($response->getId());

        // Assert
        $this->assertNotNull($result);

        $this->assertCount(1, $result['ANSWERS']);

        $this->assertEquals(
            'Расскажите о себе',
            $result['ANSWERS'][0]['QUESTION_TITLE']
        );

        $this->assertEquals(
            'Я PHP-разработчик',
            $result['ANSWERS'][0]['TEXT_VALUE']
        );

        $this->assertNull(
            $result['ANSWERS'][0]['ANSWER_TITLE']
        );
    }

    public function testCanFilterResponsesBySurvey(): void
    {
        // Arrange
        $survey1 = SurveyTable::add([
            'TITLE' => 'TEST_Опрос 1',
            'ACTIVE' => true,
        ]);

        $survey2 = SurveyTable::add([
            'TITLE' => 'TEST_Опрос 2',
            'ACTIVE' => true,
        ]);

        $response1 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey1->getId(),
            'USER_ID' => 1,
        ]);

        SurveyResponseTable::add([
            'SURVEY_ID' => $survey2->getId(),
            'USER_ID' => 2,
        ]);

        $responses = SurveyService::getResponses(
            $survey1->getId()
        );

        $this->assertCount(1, $responses);

        $this->assertSame(
            $response1->getId(),
            (int)$responses[0]['ID']
        );

        $this->assertSame(
            $survey1->getId(),
            (int)$responses[0]['SURVEY_ID']
        );
    }

    public function testCanFilterResponsesByUser(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для фильтра пользователя',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $surveyId = $survey->getId();

        $response1 = SurveyResponseTable::add([
            'SURVEY_ID' => $surveyId,
            'USER_ID' => 987654321,
        ]);

        SurveyResponseTable::add([
            'SURVEY_ID' => $surveyId,
            'USER_ID' => 987654322,
        ]);

        // Act
        $responses = SurveyService::getResponses(
            null,
            987654321
        );

        // Assert
        $this->assertCount(1, $responses);

        $this->assertSame(
            $response1->getId(),
            (int)$responses[0]['ID']
        );

        $this->assertSame(
            987654321,
            (int)$responses[0]['USER_ID']
        );
    }

    public function testCanFilterResponsesByDate(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для фильтра по дате',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $surveyId = $survey->getId();

        $oldResponse = SurveyResponseTable::add([
            'SURVEY_ID' => $surveyId,
            'USER_ID' => 100001,
            'CREATED_AT' => new \Bitrix\Main\Type\DateTime('01.01.2026 10:00:00'),
        ]);

        $newResponse = SurveyResponseTable::add([
            'SURVEY_ID' => $surveyId,
            'USER_ID' => 100002,
            'CREATED_AT' => new \Bitrix\Main\Type\DateTime('15.09.2026 10:00:00'),
        ]);

        // Act
        $responses = SurveyService::getResponses(
            $surveyId,
            null,
            new \Bitrix\Main\Type\DateTime('01.09.2026 00:00:00'),
            new \Bitrix\Main\Type\DateTime('30.09.2026 23:59:59'),
        );

        // Assert
        $this->assertCount(1, $responses);

        $this->assertSame(
            $newResponse->getId(),
            (int)$responses[0]['ID']
        );
    }

    public function testCanGetBasicStatistics(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для статистики',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Любите колу?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $this->assertTrue($question->isSuccess());

        $optionYes = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'Да',
            'SORT' => 100,
        ]);

        $optionNo = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'Нет',
            'SORT' => 200,
        ]);

        $response1 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 1001,
        ]);

        $response2 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 1002,
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response1->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $optionYes->getId(),
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response2->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $optionNo->getId(),
        ]);

        // Act
        $statistics = SurveyService::getStatistics($survey->getId());

        // Assert
        $this->assertSame(2, $statistics['TOTAL_RESPONSES']);

        $this->assertSame(
            1,
            $statistics['QUESTIONS'][0]['OPTIONS'][0]['COUNT']
        );

        $this->assertSame(
            1,
            $statistics['QUESTIONS'][0]['OPTIONS'][1]['COUNT']
        );

        $this->assertSame(
            50.0,
            $statistics['QUESTIONS'][0]['OPTIONS'][0]['PERCENT']
        );

        $this->assertSame(
            50.0,
            $statistics['QUESTIONS'][0]['OPTIONS'][1]['PERCENT']
        );

    }

    public function testCanGetTextQuestionStatistics(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Статистика текстового вопроса',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Расскажите о себе',
            'TYPE' => 'text',
            'SORT' => 100,
            'REQUIRED' => false,
        ]);

        $this->assertTrue($question->isSuccess());

        $response1 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 2001,
        ]);

        $response2 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 2002,
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response1->getId(),
            'QUESTION_ID' => $question->getId(),
            'TEXT_VALUE' => 'Первый ответ',
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response2->getId(),
            'QUESTION_ID' => $question->getId(),
            'TEXT_VALUE' => 'Второй ответ',
        ]);

        // Act
        $statistics = SurveyService::getStatistics($survey->getId());

        // Assert
        $this->assertSame(2, $statistics['TOTAL_RESPONSES']);

        $this->assertSame(
            2,
            $statistics['QUESTIONS'][0]['ANSWERED_COUNT']
        );
    }

    public function testCanGetStatisticsForMultipleQuestions(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Статистика нескольких вопросов',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $question1 = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Любите колу?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $this->assertTrue($question1->isSuccess());

        $optionYes = AnswerOptionTable::add([
            'QUESTION_ID' => $question1->getId(),
            'TITLE' => 'Да',
            'SORT' => 100,
        ]);

        $optionNo = AnswerOptionTable::add([
            'QUESTION_ID' => $question1->getId(),
            'TITLE' => 'Нет',
            'SORT' => 200,
        ]);

        $question2 = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Что вам нравится?',
            'TYPE' => 'text',
            'SORT' => 200,
            'REQUIRED' => false,
        ]);

        $this->assertTrue($question2->isSuccess());

        $response1 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 3001,
        ]);

        $response2 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 3002,
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response1->getId(),
            'QUESTION_ID' => $question1->getId(),
            'ANSWER_OPTION_ID' => $optionYes->getId(),
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response2->getId(),
            'QUESTION_ID' => $question1->getId(),
            'ANSWER_OPTION_ID' => $optionNo->getId(),
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response1->getId(),
            'QUESTION_ID' => $question2->getId(),
            'TEXT_VALUE' => 'Мне нравится вкус',
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response2->getId(),
            'QUESTION_ID' => $question2->getId(),
            'TEXT_VALUE' => 'Мне нравится газ',
        ]);

        // Act
        $statistics = SurveyService::getStatistics($survey->getId());

        // Assert
        $this->assertSame(2, $statistics['TOTAL_RESPONSES']);

        $this->assertCount(2, $statistics['QUESTIONS']);

        // Первый вопрос
        $this->assertSame(
            'Любите колу?',
            $statistics['QUESTIONS'][0]['TITLE']
        );

        $this->assertSame(
            1,
            $statistics['QUESTIONS'][0]['OPTIONS'][0]['COUNT']
        );

        $this->assertSame(
            1,
            $statistics['QUESTIONS'][0]['OPTIONS'][1]['COUNT']
        );

        // Второй вопрос
        $this->assertSame(
            'Что вам нравится?',
            $statistics['QUESTIONS'][1]['TITLE']
        );

        $this->assertSame(
            2,
            $statistics['QUESTIONS'][1]['ANSWERED_COUNT']
        );
    }

    public function testGetStatisticsForNonExistingSurvey(): void
    {
        $statistics = SurveyService::getStatistics(999999999);

        $this->assertSame(0, $statistics['TOTAL_RESPONSES']);
        $this->assertSame([], $statistics['QUESTIONS']);
    }

    public function testStatisticsAreSeparatedBySurvey(): void
    {
        // Arrange
        $survey1 = SurveyTable::add([
            'TITLE' => 'TEST_Статистика опроса 1',
            'ACTIVE' => true,
        ]);

        $survey2 = SurveyTable::add([
            'TITLE' => 'TEST_Статистика опроса 2',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey1->isSuccess());
        $this->assertTrue($survey2->isSuccess());

        $response1 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey1->getId(),
            'USER_ID' => 4001,
        ]);

        SurveyResponseTable::add([
            'SURVEY_ID' => $survey2->getId(),
            'USER_ID' => 4002,
        ]);

        // Act
        $statistics1 = SurveyService::getStatistics($survey1->getId());
        $statistics2 = SurveyService::getStatistics($survey2->getId());

        // Assert
        $this->assertSame(1, $statistics1['TOTAL_RESPONSES']);
        $this->assertSame(1, $statistics2['TOTAL_RESPONSES']);
    }

    public function testCanPrepareResultsForExport(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Опрос для экспорта',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $question1 = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Любите колу?',
            'TYPE' => 'single',
            'SORT' => 100,
            'REQUIRED' => true,
        ]);

        $this->assertTrue($question1->isSuccess());

        $optionYes = AnswerOptionTable::add([
            'QUESTION_ID' => $question1->getId(),
            'TITLE' => 'Да',
            'SORT' => 100,
        ]);

        $question2 = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Расскажите почему',
            'TYPE' => 'text',
            'SORT' => 200,
            'REQUIRED' => false,
        ]);

        $this->assertTrue($question2->isSuccess());

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 5001,
        ]);

        $this->assertTrue($response->isSuccess());

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question1->getId(),
            'ANSWER_OPTION_ID' => $optionYes->getId(),
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question2->getId(),
            'TEXT_VALUE' => 'Потому что вкусно',
        ]);

        // Act
        $rows = SurveyService::getResultsForExport($survey->getId());

        // Assert
        $this->assertCount(1, $rows);

        $this->assertSame(
            'TEST_Опрос для экспорта',
            $rows[0]['SURVEY_TITLE']
        );

        $this->assertSame(
            $response->getId(),
            (int)$rows[0]['RESPONSE_ID']
        );

        $this->assertSame(
            5001,
            (int)$rows[0]['USER_ID']
        );

        $this->assertSame(
            'Да',
            $rows[0]['ANSWERS']['Любите колу?']
        );

        $this->assertSame(
            'Потому что вкусно',
            $rows[0]['ANSWERS']['Расскажите почему']
        );
    }

    public function testCanPrepareMultipleAnswersForExport(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Экспорт multiple',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $question = QuestionTable::add([
            'SURVEY_ID' => $survey->getId(),
            'TITLE' => 'Какие напитки любите?',
            'TYPE' => 'multiple',
            'SORT' => 100,
            'REQUIRED' => false,
        ]);

        $this->assertTrue($question->isSuccess());

        $optionCola = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'Кола',
            'SORT' => 100,
        ]);

        $optionPepsi = AnswerOptionTable::add([
            'QUESTION_ID' => $question->getId(),
            'TITLE' => 'Пепси',
            'SORT' => 200,
        ]);

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 6001,
        ]);

        $this->assertTrue($response->isSuccess());

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $optionCola->getId(),
        ]);

        UserAnswerTable::add([
            'RESPONSE_ID' => $response->getId(),
            'QUESTION_ID' => $question->getId(),
            'ANSWER_OPTION_ID' => $optionPepsi->getId(),
        ]);

        // Act
        $rows = SurveyService::getResultsForExport($survey->getId());

        // Assert
        $this->assertCount(1, $rows);

        $this->assertSame(
            'Кола, Пепси',
            $rows[0]['ANSWERS']['Какие напитки любите?']
        );
    }

    public function testCanGenerateCsvForExport(): void
    {
        $rows = [
            [
                'SURVEY_TITLE' => 'TEST_Опрос',
                'RESPONSE_ID' => 401,
                'USER_ID' => 5001,
                'CREATED_AT' => '30.09.2026 18:00:00',
                'ANSWERS' => [
                    'Любите колу?' => 'Да',
                    'Какие напитки любите?' => 'Кола, Пепси',
                ],
            ],
        ];

        $csv = SurveyService::generateCsv($rows);

        $this->assertStringContainsString(
            'Опрос',
            $csv
        );

        $this->assertStringContainsString(
            'ID прохождения',
            $csv
        );

        $this->assertStringContainsString(
            'Пользователь',
            $csv
        );

        $this->assertStringContainsString(
            'Любите колу?',
            $csv
        );

        $this->assertStringContainsString(
            'Кола, Пепси',
            $csv
        );
    }

    public function testCanFilterResultsForExportByUser(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Экспорт с фильтром пользователя',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $response1 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 7001,
        ]);

        $response2 = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 7002,
        ]);

        $this->assertTrue($response1->isSuccess());
        $this->assertTrue($response2->isSuccess());

        // Act
        $rows = SurveyService::getResultsForExport(
            $survey->getId(),
            7001
        );

        // Assert
        $this->assertCount(1, $rows);

        $this->assertSame(
            $response1->getId(),
            (int)$rows[0]['RESPONSE_ID']
        );

        $this->assertSame(
            7001,
            (int)$rows[0]['USER_ID']
        );
    }

    public function testCanFilterResultsForExportByDate(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Экспорт с фильтром по дате',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $oldResponse = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 8001,
            'CREATED_AT' => new \Bitrix\Main\Type\DateTime(
                '01.01.2026 10:00:00'
            ),
        ]);

        $newResponse = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 8002,
            'CREATED_AT' => new \Bitrix\Main\Type\DateTime(
                '15.09.2026 10:00:00'
            ),
        ]);

        $this->assertTrue($oldResponse->isSuccess());
        $this->assertTrue($newResponse->isSuccess());

        // Act
        $rows = SurveyService::getResultsForExport(
            $survey->getId(),
            null,
            new \Bitrix\Main\Type\DateTime('01.09.2026 00:00:00'),
            new \Bitrix\Main\Type\DateTime('30.09.2026 23:59:59'),
        );

        // Assert
        $this->assertCount(1, $rows);

        $this->assertSame(
            $newResponse->getId(),
            (int)$rows[0]['RESPONSE_ID']
        );

        $this->assertSame(
            8002,
            (int)$rows[0]['USER_ID']
        );
    }

    public function testCanCombineExportFilters(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Комбинированный экспорт',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $matchingResponse = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 9001,
            'CREATED_AT' => new \Bitrix\Main\Type\DateTime(
                '15.09.2026 10:00:00'
            ),
        ]);

        SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 9002,
            'CREATED_AT' => new \Bitrix\Main\Type\DateTime(
                '15.09.2026 10:00:00'
            ),
        ]);

        SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 9001,
            'CREATED_AT' => new \Bitrix\Main\Type\DateTime(
                '01.01.2026 10:00:00'
            ),
        ]);

        // Act
        $rows = SurveyService::getResultsForExport(
            $survey->getId(),
            9001,
            new \Bitrix\Main\Type\DateTime('01.09.2026 00:00:00'),
            new \Bitrix\Main\Type\DateTime('30.09.2026 23:59:59'),
        );

        // Assert
        $this->assertCount(1, $rows);

        $this->assertSame(
            $matchingResponse->getId(),
            (int)$rows[0]['RESPONSE_ID']
        );

        $this->assertSame(
            9001,
            (int)$rows[0]['USER_ID']
        );
    }

    public function testCanGenerateExcelForExport(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'TEST_Excel экспорт',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($survey->isSuccess());

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $survey->getId(),
            'USER_ID' => 9003,
        ]);

        $this->assertTrue($response->isSuccess());

        // Act
        $rows = SurveyService::getResultsForExport($survey->getId());

        $excel = SurveyService::generateExcel($rows);

        // Assert
        $this->assertNotEmpty($excel);

        $this->assertStringContainsString(
            'PK',
            substr($excel, 0, 10)
        );
    }
}