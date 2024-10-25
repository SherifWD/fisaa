<?php
namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use App\Models\Trip;
use Carbon\Carbon;

class TripStatsWidget extends LineChartWidget
{
    protected static ?string $heading = 'Trip Statistics';

    // Optional: Add filters for the chart
    protected function getFilters(): array
    {
        return [
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
        ];
    }

    // Query and format the data for the chart
    protected function getData(): array
    {
        $query = Trip::query();

        // Apply filter based on selection
        switch ($this->filter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month);
                break;
        }

        $tripData = $query->selectRaw('count(id) as count, DATE(created_at) as date')
                          ->groupBy('date')
                          ->pluck('count', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'Trips',
                    'data' => $tripData->values(),
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $tripData->keys(),
        ];
    }
}
