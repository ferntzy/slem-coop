@php
    $columns = $table['columns'] ?? [];
    $rows = $table['rows'] ?? [];
    $totals = $table['totals'] ?? [];
    $title = $table['title'] ?? null;
    $totalsLabel = $table['totals_label'] ?? 'Total';
@endphp

<div class="report-table">
    @if ($title)
        <div class="report-table__heading">{{ $title }}</div>
    @endif

    <div class="report-table__scroll">
        <table class="report-table__table">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th class="report-table__head {{ ($column['align'] ?? 'left') === 'right' ? 'report-table__head--right' : '' }}">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $row)
                    <tr class="report-table__row">
                        @foreach ($columns as $column)
                            <td class="report-table__cell {{ ($column['align'] ?? 'left') === 'right' ? 'report-table__cell--right' : '' }}">
                                {{ data_get($row, $column['key']) ?? '—' }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="report-table__empty" colspan="{{ count($columns) }}">
                            No records found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if (!empty($totals))
                <tfoot>
                    <tr>
                        @foreach ($columns as $index => $column)
                            <td class="report-table__footer {{ ($column['align'] ?? 'left') === 'right' ? 'report-table__footer--right' : '' }} {{ $index === 0 ? 'report-table__footer--label' : 'report-table__footer--muted' }}">
                                @if ($index === 0)
                                    {{ $totalsLabel }}
                                @else
                                    {{ data_get($totals, $column['key']) ?? '—' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>