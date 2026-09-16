<?php

namespace App\Services;

use App\Models\Question;
use App\Models\UserAnswer;

class ExamGradingService
{
    /**
     * Centralized grading dispatcher: evaluates whether a student's answer to a question is 100% correct.
     * Supports 'single', 'multiple' (PGK), and 'statement' (Sesuai / Tidak Sesuai).
     *
     * @param Question $question
     * @param array $selectedOptionIds (used for single & multiple)
     * @param array $userValuesByOptionId (keyed by option_id => bool, used for statement)
     * @return bool
     */
    public function isQuestionCorrect(Question $question, array $selectedOptionIds, array $userValuesByOptionId = []): bool
    {
        if ($question->type === 'statement' || $question->isStatement()) {
            return $this->isStatementQuestionCorrect($question, $userValuesByOptionId);
        }

        return $this->isSetMatchCorrect($question, $selectedOptionIds);
    }

    /**
     * Grade single and multiple choice questions using All-or-Nothing set matching.
     *
     * @param Question $question
     * @param array $selectedOptionIds
     * @return bool
     */
    public function isSetMatchCorrect(Question $question, array $selectedOptionIds): bool
    {
        $correctOptionIds = $question->options
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $userOptionIds = collect($selectedOptionIds)
            ->filter(fn($id) => !empty($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (empty($correctOptionIds)) {
            return false;
        }

        return $correctOptionIds === $userOptionIds;
    }

    /**
     * Grade 'statement' (Sesuai / Tidak Sesuai) questions.
     *
     * GRADING ALGORITHM: All-or-Nothing per Statement Question
     * -------------------------------------------------------------------------
     * The question is awarded full credit (true) if and only if EVERY statement option
     * has been answered by the student AND the student's boolean value (true = Sesuai,
     * false = Tidak Sesuai) strictly matches the option's `is_correct` boolean value.
     *
     * Design Decision Note: In national TKA/UTBK examinations, statement questions
     * are sometimes evaluated either all-or-nothing (1 point per reading cluster) or
     * as fractional points per statement. Under this centralized method, transitioning
     * between all-or-nothing and partial credit in the future only requires changing
     * logic here and in total_questions / correct_count in a single location.
     *
     * @param Question $question
     * @param array<int|string, bool> $userValuesByOptionId
     * @return bool
     */
    public function isStatementQuestionCorrect(Question $question, array $userValuesByOptionId): bool
    {
        $statementOptions = $question->options;

        if ($statementOptions->isEmpty()) {
            return false;
        }

        foreach ($statementOptions as $opt) {
            // Must have answered this statement
            if (!array_key_exists($opt->id, $userValuesByOptionId) && !array_key_exists((string) $opt->id, $userValuesByOptionId)) {
                return false;
            }

            $userVal = $userValuesByOptionId[$opt->id] ?? $userValuesByOptionId[(string) $opt->id];

            // If the user's answer is null or doesn't match the expected boolean key
            if ($userVal === null || (bool) $userVal !== (bool) $opt->is_correct) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper to grade directly from a UserAnswer Eloquent instance.
     *
     * @param Question $question
     * @param UserAnswer|null $userAnswer
     * @return bool
     */
    public function gradeUserAnswer(Question $question, ?UserAnswer $userAnswer): bool
    {
        if (!$userAnswer) {
            return false;
        }

        if ($question->isStatement()) {
            $userValues = $userAnswer->userAnswerOptions
                ->pluck('value', 'option_id')
                ->all();

            return $this->isStatementQuestionCorrect($question, $userValues);
        }

        $selectedOptionIds = $userAnswer->options->pluck('id')->all();
        return $this->isSetMatchCorrect($question, $selectedOptionIds);
    }
}
