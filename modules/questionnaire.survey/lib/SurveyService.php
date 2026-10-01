<?php

namespace Questionnaire\Survey;

class SurveyService
{
    public static function getSurvey(int $surveyId): array
    {
        $survey = SurveyTable::getById($surveyId)->fetchObject();

        if (!$survey) {
            return [];
        }

        $survey->fill(['QUESTIONS']);

        $questions = [];

        foreach ($survey->getQuestions() as $question) {
            $question->fill(['OPTIONS']);

            $options = [];

            foreach ($question->getOptions() as $option) {
                $options[] = [
                    'ID' => $option->getId(),
                    'TITLE' => $option->getTitle(),
                    'SORT' => $option->getSort(),
                ];
            }

            $questions[] = [
                'ID' => $question->getId(),
                'TITLE' => $question->getTitle(),
                'TYPE' => $question->getType(),
                'SORT' => $question->getSort(),
                'REQUIRED' => $question->getRequired(),
                'OPTIONS' => $options,
            ];
        }

        return [
            'ID' => $survey->getId(),
            'TITLE' => $survey->getTitle(),
            'ACTIVE' => $survey->getActive(),
            'QUESTIONS' => $questions,
        ];
    }

    public static function deleteSurvey(int $surveyId): void
    {
        $responses = SurveyResponseTable::query()
            ->setSelect(['ID'])
            ->where('SURVEY_ID', $surveyId)
            ->fetchAll();

        foreach ($responses as $response) {
            $responseId = (int)$response['ID'];

            $answers = UserAnswerTable::query()
                ->setSelect(['ID'])
                ->where('RESPONSE_ID', $responseId)
                ->fetchAll();

            foreach ($answers as $answer) {
                UserAnswerTable::delete((int)$answer['ID']);
            }

            SurveyResponseTable::delete($responseId);
        }

        $questions = QuestionTable::query()
            ->setSelect(['ID'])
            ->where('SURVEY_ID', $surveyId)
            ->fetchAll();

        foreach ($questions as $question) {
            $questionId = (int)$question['ID'];

            $options = AnswerOptionTable::query()
                ->setSelect(['ID'])
                ->where('QUESTION_ID', $questionId)
                ->fetchAll();

            foreach ($options as $option) {
                AnswerOptionTable::delete((int)$option['ID']);
            }

            QuestionTable::delete($questionId);
        }

        SurveyTable::delete($surveyId);
    }

    public static function getResponses(
        ?int $surveyId = null,
        ?int $userId = null,
        ?\Bitrix\Main\Type\DateTime $dateFrom = null,
        ?\Bitrix\Main\Type\DateTime $dateTo = null
    ): array {
        $query = SurveyResponseTable::query()
            ->setSelect([
                'ID',
                'SURVEY_ID',
                'USER_ID',
                'CREATED_AT',
                'SURVEY_TITLE' => 'SURVEY.TITLE',
            ])
            ->setOrder(['ID' => 'DESC']);

        if ($surveyId !== null) {
            $query->where('SURVEY_ID', $surveyId);
        }

        if ($userId !== null) {
            $query->where('USER_ID', $userId);
        }

        if ($dateFrom !== null) {
            $query->where('CREATED_AT', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->where('CREATED_AT', '<=', $dateTo);
        }

        return $query->fetchAll();
    }

    public static function getResponse(int $responseId): ?array
    {
        $response = SurveyResponseTable::query()
            ->setSelect([
                'ID',
                'SURVEY_ID',
                'USER_ID',
                'CREATED_AT',
                'SURVEY_TITLE' => 'SURVEY.TITLE',
            ])
            ->where('ID', $responseId)
            ->fetch();

        if (!$response) {
            return null;
        }

        $answers = UserAnswerTable::query()
            ->setSelect([
                'ID',
                'QUESTION_ID',
                'ANSWER_OPTION_ID',
                'TEXT_VALUE',
                'QUESTION_TITLE' => 'QUESTION.TITLE',
                'ANSWER_TITLE' => 'ANSWER_OPTION.TITLE',
            ])
            ->where('RESPONSE_ID', $responseId)
            ->fetchAll();

        $response['ANSWERS'] = $answers;

        return $response;
    }

    public static function getStatistics(int $surveyId): array
    {
        $totalResponses = (int)SurveyResponseTable::query()
            ->where('SURVEY_ID', $surveyId)
            ->queryCountTotal();

        $questions = QuestionTable::query()
            ->setSelect([
                'ID',
                'TITLE',
                'TYPE',
            ])
            ->where('SURVEY_ID', $surveyId)
            ->setOrder(['SORT' => 'ASC'])
            ->fetchAll();

        $statisticsQuestions = [];

        foreach ($questions as $question) {
            $options = AnswerOptionTable::query()
                ->setSelect([
                    'ID',
                    'TITLE',
                ])
                ->where('QUESTION_ID', $question['ID'])
                ->setOrder(['SORT' => 'ASC'])
                ->fetchAll();

            $statisticsOptions = [];

            if ($question['TYPE'] === 'text') {
                $answeredCount = UserAnswerTable::query()
                    ->where('QUESTION_ID', $question['ID'])
                    ->whereNotNull('TEXT_VALUE')
                    ->queryCountTotal();

                $statisticsQuestions[] = [
                    'ID' => (int)$question['ID'],
                    'TITLE' => $question['TITLE'],
                    'TYPE' => $question['TYPE'],
                    'OPTIONS' => [],
                    'ANSWERED_COUNT' => (int)$answeredCount,
                ];

                continue;
            }

            foreach ($options as $option) {
                $count = (int)UserAnswerTable::query()
                    ->where('QUESTION_ID', $question['ID'])
                    ->where('ANSWER_OPTION_ID', $option['ID'])
                    ->queryCountTotal();

                $percent = $totalResponses > 0
                    ? round(($count / $totalResponses) * 100, 2)
                    : 0.0;

                $statisticsOptions[] = [
                    'ID' => (int)$option['ID'],
                    'TITLE' => $option['TITLE'],
                    'COUNT' => $count,
                    'PERCENT' => $percent,
                ];
            }

            $statisticsQuestions[] = [
                'ID' => (int)$question['ID'],
                'TITLE' => $question['TITLE'],
                'TYPE' => $question['TYPE'],
                'OPTIONS' => $statisticsOptions,
            ];
        }

        return [
            'TOTAL_RESPONSES' => $totalResponses,
            'QUESTIONS' => $statisticsQuestions,
        ];
    }

    public static function getResultsForExport(
        ?int $surveyId = null,
        ?int $userId = null,
        ?\Bitrix\Main\Type\DateTime $dateFrom = null,
        ?\Bitrix\Main\Type\DateTime $dateTo = null
    ): array
    {
        $query = SurveyResponseTable::query()
            ->setSelect([
                'ID',
                'SURVEY_ID',
                'USER_ID',
                'CREATED_AT',
                'SURVEY_TITLE' => 'SURVEY.TITLE',
            ])
            ->setOrder(['ID' => 'ASC']);

        if ($surveyId !== null) {
            $query->where('SURVEY_ID', $surveyId);
        }

        if ($userId !== null) {
            $query->where('USER_ID', $userId);
        }

        if ($dateFrom !== null) {
            $query->where('CREATED_AT', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->where('CREATED_AT', '<=', $dateTo);
        }

        $responses = $query->fetchAll();

        $questions = QuestionTable::query()
            ->setSelect([
                'ID',
                'TITLE',
                'TYPE',
            ])
            ->where('SURVEY_ID', $surveyId)
            ->setOrder(['SORT' => 'ASC'])
            ->fetchAll();

        $result = [];

        foreach ($responses as $response) {
            $answers = [];

            foreach ($questions as $question) {
                $answerQuery = UserAnswerTable::query()
                    ->setSelect([
                        'ANSWER_OPTION_ID',
                        'TEXT_VALUE',
                        'OPTION_TITLE' => 'ANSWER_OPTION.TITLE',
                    ])
                    ->where('RESPONSE_ID', $response['ID'])
                    ->where('QUESTION_ID', $question['ID']);

                $answerRows = $answerQuery->fetchAll();

                if (!$answerRows) {
                    $answers[$question['TITLE']] = '';
                    continue;
                }

                if ($question['TYPE'] === 'text') {
                    $answers[$question['TITLE']] = $answerRows[0]['TEXT_VALUE'] ?? '';
                    continue;
                }

                $optionTitles = [];

                foreach ($answerRows as $answerRow) {
                    if ($answerRow['OPTION_TITLE'] !== null) {
                        $optionTitles[] = $answerRow['OPTION_TITLE'];
                    }
                }

                $answers[$question['TITLE']] = implode(', ', $optionTitles);
            }

            $result[] = [
                'SURVEY_TITLE' => $response['SURVEY_TITLE'],
                'RESPONSE_ID' => (int)$response['ID'],
                'USER_ID' => (int)$response['USER_ID'],
                'CREATED_AT' => $response['CREATED_AT'],
                'ANSWERS' => $answers,
            ];
        }

        return $result;
    }

    public static function generateCsv(array $rows): string
    {
        if (!$rows) {
            return '';
        }

        $headers = [
            'Опрос',
            'ID прохождения',
            'Пользователь',
            'Дата',
        ];

        foreach ($rows[0]['ANSWERS'] as $questionTitle => $answer) {
            $headers[] = $questionTitle;
        }

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $headers, ';');

        foreach ($rows as $row) {
            $data = [
                $row['SURVEY_TITLE'],
                $row['RESPONSE_ID'],
                $row['USER_ID'],
                $row['CREATED_AT'],
            ];

            foreach ($headers as $index => $header) {
                if ($index < 4) {
                    continue;
                }

                $data[] = $row['ANSWERS'][$header] ?? '';
            }

            fputcsv($handle, $data, ';');
        }

        rewind($handle);

        $csv = stream_get_contents($handle);

        fclose($handle);

        return $csv;
    }

    public static function generateExcel(array $rows): string
    {
        if (!$rows) {
            return '';
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Опрос',
            'ID прохождения',
            'Пользователь',
            'Дата',
        ];

        foreach ($rows[0]['ANSWERS'] as $questionTitle => $answer) {
            $headers[] = $questionTitle;
        }

        foreach ($headers as $columnIndex => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                $columnIndex + 1
            );

            $sheet->setCellValue(
                $column . '1',
                $header
            );
        }

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 2;

            $data = [
                $row['SURVEY_TITLE'],
                $row['RESPONSE_ID'],
                $row['USER_ID'],
                (string)$row['CREATED_AT'],
            ];

            foreach ($headers as $index => $header) {
                if ($index < 4) {
                    continue;
                }

                $data[] = $row['ANSWERS'][$header] ?? '';
            }

            foreach ($data as $columnIndex => $value) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                    $columnIndex + 1
                );

                $sheet->setCellValue(
                    $column . $excelRow,
                    $value
                );
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');

        return ob_get_clean();
    }

}