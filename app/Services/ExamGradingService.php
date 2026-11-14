<?php

namespace App\Services;

use App\Models\Question;
use App\Models\UserAnswer;

class ExamGradingService
{
    
    public function isQuestionCorrect(Question $question, array $selectedOptionIds, array $userValuesByOptionId = []): bool
    {
        if ($question->type === 'statement' || $question->isStatement()) {
            return $this->isStatementQuestionCorrect($question, $userValuesByOptionId);
        }

        return $this->isSetMatchCorrect($question, $selectedOptionIds);
    }

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

public function isStatementQuestionCorrect(Question $question, array $userValuesByOptionId): bool
    {
        $statementOptions = $question->options;

        if ($statementOptions->isEmpty()) {
            return false;
        }

        foreach ($statementOptions as $opt) {
            
            if (!array_key_exists($opt->id, $userValuesByOptionId) && !array_key_exists((string) $opt->id, $userValuesByOptionId)) {
                return false;
            }

            $userVal = $userValuesByOptionId[$opt->id] ?? $userValuesByOptionId[(string) $opt->id];

if ($userVal === null || (bool) $userVal !== (bool) $opt->is_correct) {
                return false;
            }
        }

        return true;
    }

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
