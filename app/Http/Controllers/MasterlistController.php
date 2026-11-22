<?php

namespace App\Http\Controllers;

use App\Models\MasterlistStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterlistController extends Controller
{
    // Accepted header names for each column (lowercase, no spaces/dots/underscores)
    private const COLUMNS = [
        'student_number' => ['studentnumber', 'studentno', 'studno', 'idnumber'],
        'last_name' => ['lastname', 'surname', 'familyname'],
        'first_name' => ['firstname', 'givenname'],
        'course' => ['course', 'program'],
        'year_level' => ['yearlevel', 'year', 'yr'],
        'section' => ['section', 'sec'],
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $students = MasterlistStudent::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $op = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like'; // case-insensitive search
                $query->where(fn ($q) => $q->where('student_number', $op, $like)
                    ->orWhere('last_name', $op, $like)
                    ->orWhere('first_name', $op, $like));
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        $total = MasterlistStudent::count();
        $registered = User::whereIn('student_number', MasterlistStudent::select('student_number'))->count();

        return view('admin.masterlist.index', compact('students', 'search', 'total', 'registered'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $firstLine = (string) fgets($handle);
        rewind($handle);

        // Excel in some regions saves CSV with semicolons
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter) ?: [];
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0] ?? ''); // remove Excel's UTF-8 BOM
        $map = $this->mapColumns($header);

        if ($missing = array_diff(array_keys(self::COLUMNS), array_keys($map))) {
            fclose($handle);

            return back()->withErrors(['file' => 'Missing column(s): '.implode(', ', $missing).'. Download the template to see the correct format.']);
        }

        $rows = [];
        $errors = [];
        $line = 1;

        while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;

            if (count(array_filter($cells, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue; // skip blank lines
            }

            $row = [];
            foreach ($map as $field => $index) {
                $row[$field] = trim((string) ($cells[$index] ?? ''));
            }

            $row['student_number'] = strtoupper($row['student_number']);
            $row['course'] = strtoupper($row['course']);
            $row['section'] = strtoupper($row['section']);

            if ($problem = $this->rowProblem($row)) {
                $errors[] = "Row {$line}: {$problem}";

                continue;
            }

            $row['year_level'] = (int) $row['year_level'];
            $rows[$row['student_number']] = $row; // a repeated student number keeps the last row
        }
        fclose($handle);

        $synced = 0;

        DB::transaction(function () use ($rows, $request, &$synced) {
            foreach (array_chunk(array_values($rows), 500) as $chunk) {
                $now = now();
                $chunk = array_map(fn ($r) => $r + ['created_at' => $now, 'updated_at' => $now], $chunk);

                MasterlistStudent::upsert($chunk, ['student_number'], ['last_name', 'first_name', 'course', 'year_level', 'section', 'updated_at']);
            }

            // The registrar's data wins: update students who already registered
            if ($request->boolean('sync_users')) {
                foreach ($rows as $number => $row) {
                    $synced += User::where('student_number', $number)
                        ->where('role', 'student')
                        ->update(['course' => $row['course'], 'year_level' => $row['year_level'], 'section' => $row['section']]);
                }
            }
        });

        Log::info('Masterlist imported', [
            'by' => $request->user()->id,
            'rows' => count($rows),
            'skipped' => count($errors),
            'synced_users' => $synced,
        ]);

        $message = count($rows).' student(s) imported.';
        if ($request->boolean('sync_users')) {
            $message .= " {$synced} registered account(s) updated.";
        }

        return redirect()->route('admin.masterlist.index')
            ->with('success', $message)
            ->with('import_errors', array_slice($errors, 0, 50))
            ->with('import_error_count', count($errors));
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['student_number', 'last_name', 'first_name', 'course', 'year_level', 'section']);
            fputcsv($out, ['2023-00101-CL-0', 'Dela Cruz', 'Juan', 'BSIT', 3, '1']);
            fputcsv($out, ['2023-00102-CL-0', 'Peña', 'Ana', 'BSIT', 3, '1']);
            fclose($out);
        }, 'masterlist-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array<string, int> field name => column index */
    private function mapColumns(array $header): array
    {
        $map = [];

        foreach ($header as $index => $name) {
            $key = preg_replace('/[^a-z]/', '', strtolower((string) $name));

            foreach (self::COLUMNS as $field => $aliases) {
                if (in_array($key, $aliases, true) && ! isset($map[$field])) {
                    $map[$field] = $index;
                }
            }
        }

        return $map;
    }

    private function rowProblem(array $row): ?string
    {
        if (! preg_match(config('campus.student_number_pattern'), $row['student_number'])) {
            return "student number \"{$row['student_number']}\" is not in the format ".config('campus.student_number_example').'.';
        }
        if ($row['last_name'] === '' || $row['first_name'] === '') {
            return 'name is missing.';
        }
        if (! in_array($row['course'], config('campus.courses'), true)) {
            return "course \"{$row['course']}\" is not in config/campus.php.";
        }
        if (! ctype_digit($row['year_level']) || $row['year_level'] < 1 || $row['year_level'] > 6) {
            return 'year level must be 1 to 6.';
        }
        if ($row['section'] === '' || strlen($row['section']) > 10) {
            return 'section is missing or too long.';
        }

        return null;
    }
}
