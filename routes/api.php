<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\ReadingSession;
use App\Models\BookReadingProgress;
use App\Models\DailyActivity;
use App\Models\PointTransaction;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/reading/heartbeat', function (Request $request) {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'chapter_id' => 'nullable|exists:book_chapters,id',
            'minutes' => 'required|numeric|min:0.5|max:10',
        ]);

        $user = $request->user();
        $bookId = $request->book_id;
        $chapterId = $request->chapter_id;
        $minutes = $request->minutes;
        $today = now('Asia/Tashkent')->toDateString();

        ReadingSession::create([
            'user_id' => $user->id,
            'book_id' => $bookId,
            'chapter_id' => $chapterId,
            'minutes_read' => ceil($minutes),
            'session_date' => $today,
        ]);

        $activity = DailyActivity::firstOrCreate(
            ['user_id' => $user->id, 'activity_date' => $today],
            ['minutes_read' => 0, 'logged_in' => true, 'points_earned' => 0]
        );
        $activity->increment('minutes_read', ceil($minutes));

        if ($activity->minutes_read >= 10 && $activity->minutes_read % 10 === 0) {
            $points = config('app.reading_points_per_10min', 10);
            $user->increment('total_points', $points);
            $activity->increment('points_earned', $points);

            PointTransaction::create([
                'user_id' => $user->id,
                'points' => $points,
                'source' => 'reading',
                'description' => '10 daqiqa faol kitob o\'qish uchun rag\'bat!',
            ]);
        }

        return response()->json([
            'success' => true,
            'total_minutes_today' => $activity->minutes_read,
            'total_points' => $user->total_points,
        ]);
    });

    Route::post('/reading/progress', function (Request $request) {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'chapter_id' => 'nullable|exists:book_chapters,id',
            'last_position' => 'required|integer',
            'percent_complete' => 'required|numeric|min:0|max:100',
        ]);

        $user = $request->user();
        $progress = BookReadingProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'book_id' => $request->book_id,
                'chapter_id' => $request->chapter_id,
            ],
            [
                'last_position' => $request->last_position,
                'percent_complete' => $request->percent_complete,
            ]
        );

        return response()->json(['success' => true, 'progress' => $progress]);
    });
});
