<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class QuizAuthoringService
{
    public function update(Request $request, Quiz $quiz, int $maxRewardPoints): Quiz
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'reward_points' => "required|integer|min:5|max:{$maxRewardPoints}",
            'description' => 'nullable|string|max:1000',
            'difficulty' => 'required|in:easy,medium,hard',
            'time_limit_minutes' => 'nullable|integer|min:1|max:180',
            'questions' => 'required|array|min:1|max:100',
            'questions.*.id' => 'nullable|integer',
            'questions.*.text' => 'required|string|min:3|max:5000',
            'questions.*.type' => 'required|in:single,text',
            'questions.*.expected_answer' => 'nullable|string|max:10000',
            'questions.*.image_shape' => 'nullable|in:rectangle,square,circle,rounded',
            'questions.*.options' => 'nullable|array|min:2|max:10',
            'questions.*.options.*' => 'nullable|string|max:1000',
            'questions.*.correct' => 'nullable|integer|min:0|max:9',
            'questions.*.explanation' => 'nullable|string|max:2000',
            'questions.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'questions.required' => 'Kamida bitta savol qoldiring.',
            'questions.*.image.image' => 'Savol rasmi JPG, PNG yoki WEBP formatida bo‘lishi kerak.',
            'questions.*.image.max' => 'Savol rasmi 5 MB dan oshmasligi kerak.',
        ]);

        $validator->after(function ($validator) use ($request, $quiz) {
            $knownIds = $quiz->questions()->pluck('id')->map(fn ($id) => (int) $id)->all();
            $submittedIds = [];

            foreach ((array) $request->input('questions', []) as $index => $question) {
                if (!is_array($question)) {
                    continue;
                }

                if (!empty($question['id'])) {
                    $questionId = (int) $question['id'];
                    if (!in_array($questionId, $knownIds, true) || in_array($questionId, $submittedIds, true)) {
                        $validator->errors()->add("questions.{$index}.id", 'Savol ushbu testga tegishli emas yoki takrorlangan.');
                    }
                    $submittedIds[] = $questionId;
                }

                if (($question['type'] ?? 'single') !== 'single') {
                    continue;
                }

                $rawOptions = is_array($question['options'] ?? null) ? $question['options'] : [];
                $options = array_filter($rawOptions, fn ($option) => is_string($option) && trim($option) !== '');
                if (count($options) < 2) {
                    $validator->errors()->add("questions.{$index}.options", 'Variantli savol uchun kamida ikkita javob kiriting.');
                }

                $correct = $question['correct'] ?? null;
                if ($correct === null || !isset($rawOptions[$correct]) || trim((string) $rawOptions[$correct]) === '') {
                    $validator->errors()->add("questions.{$index}.correct", 'To‘g‘ri javobni belgilang.');
                }
            }
        });

        $data = $validator->validate();
        $questions = $data['questions'];
        $rewardPoints = (int) $data['reward_points'];
        $perQuestionPoints = max(1, (int) round($rewardPoints / count($questions)));
        $removedImages = [];

        DB::transaction(function () use ($request, $quiz, $data, $questions, $rewardPoints, $perQuestionPoints, &$removedImages) {
            $quiz->update([
                'title' => trim($data['title']),
                'description' => isset($data['description']) ? trim($data['description']) : null,
                'difficulty' => $data['difficulty'],
                'reward_points' => $rewardPoints,
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            ]);

            $existingQuestions = $quiz->questions()->with('options')->get()->keyBy('id');
            $retainedIds = [];

            foreach ($questions as $index => $questionData) {
                $question = !empty($questionData['id'])
                    ? $existingQuestions->get((int) $questionData['id'])
                    : null;

                $imagePath = $question?->image_path;
                if ($request->hasFile("questions.{$index}.image")) {
                    if ($imagePath) {
                        $removedImages[] = $imagePath;
                    }
                    $imagePath = $request->file("questions.{$index}.image")->store('quizzes/questions', 'public');
                }

                $attributes = [
                    'question_text' => trim($questionData['text']),
                    'image_path' => $imagePath,
                    'image_shape' => $questionData['image_shape'] ?? 'rectangle',
                    'type' => $questionData['type'],
                    'expected_answer' => filled($questionData['expected_answer'] ?? null) ? trim($questionData['expected_answer']) : null,
                    'points' => $perQuestionPoints,
                    'explanation' => filled($questionData['explanation'] ?? null) ? trim($questionData['explanation']) : null,
                    'order' => $index + 1,
                ];

                if ($question) {
                    $question->update($attributes);
                    $retainedIds[] = $question->id;
                } else {
                    $question = $quiz->questions()->create($attributes);
                }

                $question->options()->delete();
                if ($questionData['type'] !== 'single') {
                    continue;
                }

                $correctIndex = (int) $questionData['correct'];
                foreach (($questionData['options'] ?? []) as $optionIndex => $optionText) {
                    if (trim((string) $optionText) === '') {
                        continue;
                    }
                    $question->options()->create([
                        'option_text' => trim($optionText),
                        'is_correct' => (int) $optionIndex === $correctIndex,
                        'order' => $optionIndex + 1,
                    ]);
                }
            }

            $existingQuestions->each(function ($question) use ($retainedIds, &$removedImages) {
                if (in_array($question->id, $retainedIds, true)) {
                    return;
                }
                if ($question->image_path) {
                    $removedImages[] = $question->image_path;
                }
                $question->delete();
            });
        });

        foreach (array_unique($removedImages) as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return $quiz->refresh();
    }

    public function create(Request $request, int $maxRewardPoints, ?int $creatorId = null): Quiz
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'nullable|exists:books,id',
            'title' => 'required|string|max:255',
            'reward_points' => "required|integer|min:5|max:{$maxRewardPoints}",
            'description' => 'nullable|string|max:1000',
            'difficulty' => 'required|in:easy,medium,hard',
            'time_limit_minutes' => 'nullable|integer|min:1|max:180',
            'questions' => 'required|array|min:1|max:100',
            'questions.*.text' => 'required|string|min:3|max:5000',
            'questions.*.type' => 'required|in:single,text',
            'questions.*.expected_answer' => 'nullable|string|max:10000',
            'questions.*.image_shape' => 'nullable|in:rectangle,square,circle,rounded',
            'questions.*.options' => 'nullable|array|min:2|max:10',
            'questions.*.options.*' => 'nullable|string|max:1000',
            'questions.*.correct' => 'nullable|integer|min:0|max:9',
            'questions.*.explanation' => 'nullable|string|max:2000',
            'questions.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'questions.required' => 'Kamida bitta savol kiriting.',
            'questions.*.image.image' => 'Savol rasmi JPG, PNG yoki WEBP formatida bo‘lishi kerak.',
            'questions.*.image.max' => 'Savol rasmi 5 MB dan oshmasligi kerak.',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ((array) $request->input('questions', []) as $index => $question) {
                if (!is_array($question)) {
                    continue;
                }
                if (($question['type'] ?? 'single') !== 'single') {
                    continue;
                }

                $rawOptions = is_array($question['options'] ?? null) ? $question['options'] : [];
                $options = array_values(array_filter(
                    $rawOptions,
                    fn ($option) => is_string($option) && trim($option) !== ''
                ));
                if (count($options) < 2) {
                    $validator->errors()->add("questions.{$index}.options", 'Variantli savol uchun kamida ikkita javob kiriting.');
                }

                $correct = $question['correct'] ?? null;
                if ($correct === null || !isset($rawOptions[$correct]) || trim((string) $rawOptions[$correct]) === '') {
                    $validator->errors()->add("questions.{$index}.correct", 'To‘g‘ri javobni belgilang.');
                }
            }
        });

        $data = $validator->validate();
        $questions = $data['questions'];
        $rewardPoints = (int) $data['reward_points'];
        $perQuestionPoints = max(1, (int) round($rewardPoints / count($questions)));

        return DB::transaction(function () use ($request, $data, $questions, $rewardPoints, $perQuestionPoints, $creatorId) {
            $quiz = Quiz::create([
                'book_id' => $data['book_id'] ?? null,
                'created_by' => $creatorId,
                'chapter_number' => $request->input('chapter_number'),
                'title' => trim($data['title']),
                'description' => isset($data['description']) ? trim($data['description']) : null,
                'difficulty' => $data['difficulty'],
                'reward_points' => $rewardPoints,
                'time_limit_minutes' => $data['time_limit_minutes'] ?? 15,
                'is_active' => $request->has('is_active') || $request->input('is_active', 1) == 1,
            ]);

            foreach ($questions as $index => $questionData) {
                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => trim($questionData['text']),
                    'image_path' => $request->file("questions.{$index}.image")?->store('quizzes/questions', 'public'),
                    'image_shape' => $questionData['image_shape'] ?? 'rectangle',
                    'type' => $questionData['type'],
                    'expected_answer' => filled($questionData['expected_answer'] ?? null) ? trim($questionData['expected_answer']) : null,
                    'points' => $perQuestionPoints,
                    'explanation' => filled($questionData['explanation'] ?? null) ? trim($questionData['explanation']) : null,
                    'order' => $index + 1,
                ]);

                if ($questionData['type'] !== 'single') {
                    continue;
                }

                $correctIndex = (int) $questionData['correct'];
                foreach ($questionData['options'] as $optionIndex => $optionText) {
                    if (trim((string) $optionText) === '') {
                        continue;
                    }
                    QuizOption::create([
                        'question_id' => $question->id,
                        'option_text' => trim($optionText),
                        'is_correct' => $optionIndex === $correctIndex,
                        'order' => $optionIndex + 1,
                    ]);
                }
            }

            return $quiz;
        });
    }
}
