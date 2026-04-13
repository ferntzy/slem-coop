@php
    $record = $getRecord();
    $documents = is_array($record->cashflow_documents) ? $record->cashflow_documents : [];
    $disk = \Illuminate\Support\Facades\Storage::disk('public_storage_folder');

    $isImage = function (string $file): bool {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
    };

    $isPdf = function (string $file): bool {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return $ext === 'pdf';
    };
@endphp

<div class="flex flex-col gap-4">
    <div class="text-base font-bold text-emerald-700 dark:text-emerald-400">
        Cash Flow Supporting Documents
    </div>

    @if (empty($documents))
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 px-4 py-3 text-sm text-gray-600 dark:border-emerald-900/40 dark:bg-gray-900 dark:text-gray-300">
            No documents uploaded.
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($documents as $index => $file)
                @php
                    $url = $disk->url($file);
                    $filename = basename($file);
                @endphp

                <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm dark:border-emerald-900/40 dark:bg-gray-900">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Document {{ $index + 1 }}
                        </div>

                        <div class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            {{ strtoupper(pathinfo($file, PATHINFO_EXTENSION)) ?: 'FILE' }}
                        </div>
                    </div>

                    @if ($isImage($file))
                        <a href="{{ $url }}" target="_blank" class="block">
                            <img
                                src="{{ $url }}"
                                alt="{{ $filename }}"
                                class="h-44 w-full rounded-xl border border-emerald-100 object-cover dark:border-emerald-900/40"
                            >
                        </a>
                    @elseif ($isPdf($file))
                        <div class="flex h-44 w-full items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-sm font-semibold text-emerald-700 dark:border-emerald-900/40 dark:bg-gray-800 dark:text-emerald-300">
                            PDF Document
                        </div>
                    @else
                        <div class="flex h-44 w-full items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            File Preview Unavailable
                        </div>
                    @endif

                    <div class="mt-3 break-all text-xs text-gray-500 dark:text-gray-400">
                        {{ $filename }}
                    </div>

                    <a
                        href="{{ $url }}"
                        target="_blank"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                    >
                        Open File
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>