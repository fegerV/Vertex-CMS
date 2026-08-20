@extends('admin.layouts.app')

@section('title', 'Web Vitals Dashboard')
@section('page-title', 'Core Web Vitals Monitoring')

@section('content')
<div class="space-y-6">
    {{-- Overall Score Card --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 mb-2">Overall Score</div>
            <div class="text-3xl font-bold {{ $overallScore >= 75 ? 'text-green-600' : ($overallScore >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $overallScore }}%
            </div>
            <div class="text-xs text-gray-400 mt-1">Good measurements</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 mb-2">Total Measurements</div>
            <div class="text-3xl font-bold text-blue-600">{{ number_format($totalMeasurements) }}</div>
            <div class="text-xs text-gray-400 mt-1">All time</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 mb-2">Poor Ratings</div>
            <div class="text-3xl font-bold text-red-600">
                {{ $recentMetrics->where('rating', 'poor')->count() }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Last 20 entries</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 mb-2">Top URLs Tracked</div>
            <div class="text-3xl font-bold text-purple-600">{{ $topUrls->unique('url')->count() }}</div>
            <div class="text-xs text-gray-400 mt-1">Pages monitored</div>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-5 gap-4">
        @foreach($metrics as $type => $metric)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-gray-800">{{ $type }}</h3>
                @php
                    $avgValue = $metric['average'] ?? 0;
                    $colorClass = match($type) {
                        'LCP' => $avgValue <= 2.5 ? 'text-green-600' : ($avgValue <= 4.0 ? 'text-yellow-600' : 'text-red-600'),
                        'FID', 'INP' => $avgValue <= 0.2 ? 'text-green-600' : ($avgValue <= 0.5 ? 'text-yellow-600' : 'text-red-600'),
                        'CLS' => $avgValue <= 0.1 ? 'text-green-600' : ($avgValue <= 0.25 ? 'text-yellow-600' : 'text-red-600'),
                        default => 'text-gray-600',
                    };
                @endphp
            </div>
            <div class="text-2xl font-bold {{ $colorClass }}">
                {{ $avgValue ? round($avgValue, 3) : 'N/A' }}{{ $metric['unit'] }}
            </div>
            <div class="text-xs text-gray-500 mb-3">{{ $metric['name'] }}</div>
            
            {{-- Rating Distribution --}}
            <div class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-green-600">● Good</span>
                    <span>{{ $metric['distribution']['good'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-yellow-600">● Needs Improvement</span>
                    <span>{{ $metric['distribution']['needs-improvement'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-red-600">● Poor</span>
                    <span>{{ $metric['distribution']['poor'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">LCP Trend (7 days)</h3>
                <select id="lcpPeriod" class="text-sm border rounded px-2 py-1" onchange="loadChartData('LCP', this.value)">
                    <option value="7">7 days</option>
                    <option value="14">14 days</option>
                    <option value="30">30 days</option>
                </select>
            </div>
            <canvas id="lcpChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">CLS Trend (7 days)</h3>
                <select id="clsPeriod" class="text-sm border rounded px-2 py-1" onchange="loadChartData('CLS', this.value)">
                    <option value="7">7 days</option>
                    <option value="14">14 days</option>
                    <option value="30">30 days</option>
                </select>
            </div>
            <canvas id="clsChart" height="200"></canvas>
        </div>
    </div>

    {{-- Top URLs Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold text-gray-800">Top Pages by Measurements</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metric</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Count</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topUrls as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 truncate max-w-xs">{{ $item->url ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ $item->metric_type }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ round($item->avg_value, 3) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No data available yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Poor Ratings --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Recent Poor Ratings (Needs Attention)</h3>
            <a href="{{ route('admin.web-vitals.export') }}" class="text-sm text-blue-600 hover:underline">Export CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metric</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentMetrics as $metric)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $metric->measured_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 truncate max-w-xs">{{ $metric->url ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $metric->metric_type === 'LCP' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $metric->metric_type === 'CLS' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $metric->metric_type === 'INP' ? 'bg-pink-100 text-pink-800' : '' }}
                                {{ $metric->metric_type === 'FID' ? 'bg-cyan-100 text-cyan-800' : '' }}
                                {{ $metric->metric_type === 'TTFB' ? 'bg-indigo-100 text-indigo-800' : '' }}
                            ">{{ $metric->metric_type }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $metric->value }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $metric->rating === 'good' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $metric->rating === 'needs-improvement' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $metric->rating === 'poor' ? 'bg-red-100 text-red-800' : '' }}
                            ">{{ ucfirst(str_replace('-', ' ', $metric->rating)) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No poor ratings found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let lcpChart, clsChart;

document.addEventListener('DOMContentLoaded', function() {
    loadChartData('LCP', 7);
    loadChartData('CLS', 7);
});

function loadChartData(metricType, days) {
    fetch(`/admin/web-vitals/chart-data?type=${metricType}&days=${days}`)
        .then(response => response.json())
        .then(data => {
            const labels = data.data.map(item => item.date);
            const values = data.data.map(item => parseFloat(item.avg_value));
            
            if (metricType === 'LCP') {
                if (lcpChart) lcpChart.destroy();
                const ctx = document.getElementById('lcpChart').getContext('2d');
                lcpChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'LCP (seconds)',
                            data: values,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            annotation: {
                                annotations: {
                                    line1: {
                                        type: 'line',
                                        yMin: 2.5,
                                        yMax: 2.5,
                                        borderColor: 'rgb(34, 197, 94)',
                                        borderWidth: 2,
                                        borderDash: [5, 5],
                                        label: { content: 'Good threshold (2.5s)' }
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: false }
                        }
                    }
                });
            } else if (metricType === 'CLS') {
                if (clsChart) clsChart.destroy();
                const ctx = document.getElementById('clsChart').getContext('2d');
                clsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'CLS',
                            data: values,
                            borderColor: 'rgb(249, 115, 22)',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        })
        .catch(error => console.error('Error loading chart data:', error));
}
</script>
@endpush
@endsection
