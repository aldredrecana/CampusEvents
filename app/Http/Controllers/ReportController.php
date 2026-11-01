<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function event(Request $request, Event $event)
    {
        if (! $event->canBeManagedBy($request->user())) {
            abort(403);
        }

        $attendances = $event->attendances()->with(['user', 'scanner'])->get()->keyBy('user_id');
        $students = $event->eligibleStudents()->orderBy('section')->orderBy('name')->get();
        $ended = $event->ends_at->isPast();

        $rows = [];

        foreach ($students as $student) {
            $rows[] = $this->row($student, $attendances->get($student->id), $ended);
        }

        // Anyone checked in who is no longer in the audience (for example, the audience was edited later)
        foreach ($attendances as $userId => $attendance) {
            if ($attendance->checked_in_at && ! $students->contains('id', $userId)) {
                $rows[] = $this->row($attendance->user, $attendance, $ended);
            }
        }

        $filename = Str::slug($event->title) . '-attendance.csv';

        return response()->streamDownload(function () use ($rows, $event) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows names like "Peña" correctly

            fputcsv($out, ['Event', $this->safe($event->title)]);
            fputcsv($out, ['Venue', $this->safe($event->venue)]);
            fputcsv($out, ['Schedule', $event->starts_at->format('M d, Y h:i A') . ' to ' . $event->ends_at->format('M d, Y h:i A')]);
            fputcsv($out, []);
            fputcsv($out, ['Student No.', 'Name', 'Course', 'Year', 'Section', 'Status', 'Check-in', 'Check-out', 'Source', 'Scanned by', 'Note']);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function row($student, $attendance, bool $ended): array
    {
        $checkedIn = $attendance && $attendance->checked_in_at;
        $status = $checkedIn ? ucfirst($attendance->status) : ($ended ? 'Absent' : 'Not yet in');

        return [
            $this->safe($student->student_number),
            $this->safe($student->name),
            $this->safe($student->course),
            $student->year_level,
            $this->safe($student->section),
            $status,
            $checkedIn ? $attendance->checked_in_at->format('Y-m-d H:i') : '',
            $attendance?->checked_out_at?->format('Y-m-d H:i') ?? '',
            $checkedIn ? strtoupper($attendance->source) : '',
            $this->safe($attendance?->scanner?->name),
            $this->safe($attendance?->note),
        ];
    }

    // Stops spreadsheet formulas ("=...", "+...") typed into names or notes from running in Excel
    private function safe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}