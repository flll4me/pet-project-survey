<?php

namespace Questionnaire\Survey;

class SurveySubmitService
{
    public static function submit(
        int $surveyId,
        int $userId,
        array $answers
    ): int {

        $questions = QuestionTable::query()
            ->setSelect(['ID', 'REQUIRED'])
            ->where('SURVEY_ID', $surveyId)
            ->fetchAll();

        foreach ($questions as $question) {
            if (
                $question['REQUIRED']
                && (
                    !array_key_exists($question['ID'], $answers)
                    || $answers[$question['ID']] === ''
                    || $answers[$question['ID']] === []
                )
            ) {
                throw new \Exception(
                    'Required question has no answer'
                );
            }
        }

        $response = SurveyResponseTable::add([
            'SURVEY_ID' => $surveyId,
            'USER_ID' => $userId,
        ]);

        foreach ($answers as $questionId => $answer) {
            $question = QuestionTable::getById($questionId)->fetch();

            if (!$question) {
                throw new \Exception('Question not found');
            }

            if ($question['SURVEY_ID'] != $surveyId) {
                throw new \Exception(
                    'Question does not belong to survey'
                );
            }

            if ($question['TYPE'] === 'text') {
                UserAnswerTable::add([
                    'RESPONSE_ID' => $response->getId(),
                    'QUESTION_ID' => $questionId,
                    'TEXT_VALUE' => $answer,
                ]);
            } elseif ($question['TYPE'] === 'multiple') {
                foreach ($answer as $answerOptionId) {
                    $option = AnswerOptionTable::getById($answerOptionId)->fetch();

                    if (!$option || $option['QUESTION_ID'] != $questionId) {
                        throw new \Exception(
                            'Answer option does not belong to question'
                        );
                    }

                    UserAnswerTable::add([
                        'RESPONSE_ID' => $response->getId(),
                        'QUESTION_ID' => $questionId,
                        'ANSWER_OPTION_ID' => $answerOptionId,
                    ]);
                }
            } else {
                $option = AnswerOptionTable::getById($answer)->fetch();

                if (!$option || $option['QUESTION_ID'] != $questionId) {
                    throw new \Exception(
                        'Answer option does not belong to question'
                    );
                }

                UserAnswerTable::add([
                    'RESPONSE_ID' => $response->getId(),
                    'QUESTION_ID' => $questionId,
                    'ANSWER_OPTION_ID' => $answer,
                ]);
            }
        }

        return $response->getId();
    }
}