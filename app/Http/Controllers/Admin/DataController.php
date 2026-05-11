<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intrusion;
use App\Models\IntrusionLabel;
use App\Models\Subdomain;
use App\Models\SubdomainLabel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;

class DataController extends Controller
{
    public function intrusions(Request $request)
    {
        if ($request->ajax()) {
            $query = Intrusion::select([
                'id',
                'word_1',
                'word_2',
                'word_3',
                'word_4',
                'word_5',
                'word_6',
                'outlier_id',
                'created_at',
            ])->latest('id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->make(true);
        }

        $intrusionLabelUsers = User::query()
            ->select('users.id', 'users.email')
            ->join('intrusion_labels', 'intrusion_labels.user_id', '=', 'users.id')
            ->selectRaw('COUNT(intrusion_labels.id) as labels_count')
            ->groupBy('users.id', 'users.email')
            ->orderBy('users.email')
            ->get();

        return view('admin.data.intrusions.index', compact('intrusionLabelUsers'));
    }

    public function importIntrusions(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $rows = $this->readCsvRows($request->file('csv_file')->getRealPath());
        $inserted = 0;

        foreach ($rows as $row) {
            if ($this->isHeaderRow($row, ['outlier', 'word'])) {
                continue;
            }

            $normalized = array_pad($row, 7, null);
            $isEmptyRow = trim(implode('', array_map(fn ($v) => (string) ($v ?? ''), $normalized))) === '';

            if ($isEmptyRow) {
                continue;
            }

            Intrusion::create([
                'word_1' => $this->cleanCsvValue($normalized[0]),
                'word_2' => $this->cleanCsvValue($normalized[1]),
                'word_3' => $this->cleanCsvValue($normalized[2]),
                'word_4' => $this->cleanCsvValue($normalized[3]),
                'word_5' => $this->cleanCsvValue($normalized[4]),
                'word_6' => $this->cleanCsvValue($normalized[5]),
                'outlier_id' => $this->toNullableInt($normalized[6]),
            ]);

            $inserted++;
        }

        return back()->with('success', "Import intrusions thành công: {$inserted} dòng.");
    }

    public function clearIntrusions(): RedirectResponse
    {
        Intrusion::truncate();

        return back()->with('success', 'Đã xoá toàn bộ dữ liệu intrusions.');
    }

    public function subdomains(Request $request)
    {
        if ($request->ajax()) {
            $query = Subdomain::select([
                'id',
                'child',
                'parent',
                'label',
                'created_at',
            ])->latest('id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('label_badge', function ($row) {
                    return $row->label === 'yes'
                        ? '<span class="badge bg-success">yes</span>'
                        : '<span class="badge bg-secondary">no</span>';
                })
                ->rawColumns(['label_badge'])
                ->make(true);
        }

        $subdomainLabelUsers = User::query()
            ->select('users.id', 'users.email')
            ->join('subdomain_labels', 'subdomain_labels.user_id', '=', 'users.id')
            ->selectRaw('COUNT(subdomain_labels.id) as labels_count')
            ->groupBy('users.id', 'users.email')
            ->orderBy('users.email')
            ->get();

        return view('admin.data.subdomains.index', compact('subdomainLabelUsers'));
    }

    public function importSubdomains(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $rows = $this->readCsvRows($request->file('csv_file')->getRealPath());
        $inserted = 0;

        foreach ($rows as $row) {
            if ($this->isHeaderRow($row, ['child', 'parent'])) {
                continue;
            }

            $normalized = array_pad($row, 3, null);
            $child = $this->cleanCsvValue($normalized[0]);
            $parent = $this->cleanCsvValue($normalized[1]);

            if ($child === null || $parent === null) {
                continue;
            }

            Subdomain::create([
                'child' => $child,
                'parent' => $parent,
                'label' => $this->normalizeLabel($normalized[2]),
            ]);

            $inserted++;
        }

        return back()->with('success', "Import subdomains thành công: {$inserted} dòng.");
    }

    public function clearSubdomains(): RedirectResponse
    {
        Subdomain::truncate();

        return back()->with('success', 'Đã xoá toàn bộ dữ liệu subdomains.');
    }

    public function exportIntrusionLabels(User $user): StreamedResponse
    {
        $labels = IntrusionLabel::with('intrusion')
            ->where('user_id', $user->id)
            ->orderBy('intrusion_id')
            ->get();

        $fileName = $this->buildExportFilename($user->email, 'intrusion_labels');

        return Response::streamDownload(function () use ($labels, $user) {
            echo $this->buildIntrusionCsvContent($labels, $user->email);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportSubdomainLabels(User $user): StreamedResponse
    {
        $labels = SubdomainLabel::with('subdomain')
            ->where('user_id', $user->id)
            ->orderBy('subdomain_id')
            ->get();

        $fileName = $this->buildExportFilename($user->email, 'subdomain_labels');

        return Response::streamDownload(function () use ($labels, $user) {
            echo $this->buildSubdomainCsvContent($labels, $user->email);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportAllIntrusionLabels(): BinaryFileResponse|RedirectResponse
    {
        $users = User::query()
            ->select('users.id', 'users.email')
            ->join('intrusion_labels', 'intrusion_labels.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.email')
            ->orderBy('users.email')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Chưa có dữ liệu gắn nhãn intrusions để xuất.');
        }

        $zipPath = $this->buildZipFile(function (ZipArchive $zip) use ($users) {
            foreach ($users as $user) {
                $labels = IntrusionLabel::with('intrusion')
                    ->where('user_id', $user->id)
                    ->orderBy('intrusion_id')
                    ->get();

                $zip->addFromString(
                    $this->buildExportFilename($user->email, 'intrusion_labels'),
                    $this->buildIntrusionCsvContent($labels, $user->email)
                );
            }
        }, 'all_intrusion_labels.zip');

        return response()->download($zipPath, 'all_intrusion_labels.zip')->deleteFileAfterSend(true);
    }

    public function exportAllSubdomainLabels(): BinaryFileResponse|RedirectResponse
    {
        $users = User::query()
            ->select('users.id', 'users.email')
            ->join('subdomain_labels', 'subdomain_labels.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.email')
            ->orderBy('users.email')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Chưa có dữ liệu gắn nhãn subdomains để xuất.');
        }

        $zipPath = $this->buildZipFile(function (ZipArchive $zip) use ($users) {
            foreach ($users as $user) {
                $labels = SubdomainLabel::with('subdomain')
                    ->where('user_id', $user->id)
                    ->orderBy('subdomain_id')
                    ->get();

                $zip->addFromString(
                    $this->buildExportFilename($user->email, 'subdomain_labels'),
                    $this->buildSubdomainCsvContent($labels, $user->email)
                );
            }
        }, 'all_subdomain_labels.zip');

        return response()->download($zipPath, 'all_subdomain_labels.zip')->deleteFileAfterSend(true);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function readCsvRows(string $path): array
    {
        $rows = [];

        if (($handle = fopen($path, 'r')) === false) {
            return $rows;
        }

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = array_map(function ($value) {
                if ($value === null) {
                    return null;
                }

                return trim((string) $value);
            }, $data);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param array<int, string|null> $row
     * @param array<int, string> $needles
     */
    private function isHeaderRow(array $row, array $needles): bool
    {
        $joined = strtolower(implode(' ', array_filter($row, fn ($v) => $v !== null)));

        foreach ($needles as $needle) {
            if (!str_contains($joined, strtolower($needle))) {
                return false;
            }
        }

        return $joined !== '';
    }

    private function cleanCsvValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizeLabel(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['yes', 'y', '1', 'true'], true) ? 'yes' : 'no';
    }

    private function toNullableInt(?string $value): ?int
    {
        $cleaned = $this->cleanCsvValue($value);

        if ($cleaned === null || !is_numeric($cleaned)) {
            return null;
        }

        return (int) $cleaned;
    }

    private function buildExportFilename(string $email, string $tableName): string
    {
        $safeEmail = preg_replace('/[^A-Za-z0-9@._-]/', '_', $email) ?: 'user';

        return $safeEmail . '_' . $tableName . '.csv';
    }

    private function buildIntrusionCsvContent(iterable $labels, string $email): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['user_email', 'intrusion_id', 'word_1', 'word_2', 'word_3', 'word_4', 'word_5', 'word_6', 'outlier_id', 'labeled_at']);

        foreach ($labels as $label) {
            fputcsv($handle, [
                $email,
                $label->intrusion_id,
                $label->intrusion?->word_1,
                $label->intrusion?->word_2,
                $label->intrusion?->word_3,
                $label->intrusion?->word_4,
                $label->intrusion?->word_5,
                $label->intrusion?->word_6,
                $label->outlier_id,
                optional($label->updated_at)->toDateTimeString(),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }

    private function buildSubdomainCsvContent(iterable $labels, string $email): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['user_email', 'subdomain_id', 'child', 'parent', 'label', 'labeled_at']);

        foreach ($labels as $label) {
            fputcsv($handle, [
                $email,
                $label->subdomain_id,
                $label->subdomain?->child,
                $label->subdomain?->parent,
                $label->label,
                optional($label->updated_at)->toDateTimeString(),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }

    private function buildZipFile(callable $writer, string $fileName): string
    {
        $tempDir = storage_path('app/temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . uniqid(pathinfo($fileName, PATHINFO_FILENAME) . '_', true) . '.zip';
        $zip = new ZipArchive();
        $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            abort(500, 'Không thể tạo file ZIP để xuất dữ liệu.');
        }

        $writer($zip);
        $zip->close();

        return $zipPath;
    }
}
