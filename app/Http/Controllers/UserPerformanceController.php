<?php

namespace App\Http\Controllers;

use App\Models\MonitoringData;
use App\Models\Provinsi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserPerformanceController extends Controller
{
    public function index(Request $request)
    {
        // Default date range - last 30 days
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));

        // Filters
        $provinsiId = $request->get('provinsi_id');
        $userId = $request->get('user_id');
        $dataSource = $request->get('data_source');

        // Build base query
        $query = MonitoringData::with(['user', 'provinsi'])
            ->whereBetween('monitoring_data.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        // Apply filters
        if ($provinsiId) {
            $query->where('provinsi_id', $provinsiId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($dataSource) {
            $query->where('data_source', $dataSource);
        }

        // Get daily data for chart
        $dailyData = $query->clone()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Generate date range for chart (fill missing dates with 0)
        $dateRange = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        while ($currentDate->lte($endDateCarbon)) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayData = $dailyData->firstWhere('date', $dateStr);
            $dateRange[] = [
                'date' => $dateStr,
                'count' => $dayData ? $dayData->count : 0,
                'formatted_date' => $currentDate->format('d M')
            ];
            $currentDate->addDay();
        }

        // User performance statistics
        $userStats = $query->clone()
            ->selectRaw('user_id, users.name, COUNT(*) as total_posts, AVG(CASE WHEN data_source = "online" THEN 1.0 ELSE 0.0 END) * 100 as online_percentage')
            ->join('users', 'monitoring_data.user_id', '=', 'users.id')
            ->groupBy('user_id', 'users.name')
            ->orderBy('total_posts', 'desc')
            ->limit(10)
            ->get();

        // Overall statistics
        $totalPosts = $query->clone()->count();
        $onlinePosts = $query->clone()->where('data_source', 'online')->count();
        $offlinePosts = $query->clone()->where('data_source', 'offline')->count();
        $uniqueUsers = $query->clone()->distinct('user_id')->count('user_id');

        // Province statistics
        $provinceStats = $query->clone()
            ->selectRaw('provinsi_id, provinsi.nama, COUNT(*) as total_posts')
            ->join('provinsi', 'monitoring_data.provinsi_id', '=', 'provinsi.id')
            ->groupBy('provinsi_id', 'provinsi.nama')
            ->orderBy('total_posts', 'desc')
            ->limit(10)
            ->get();

        // Data source breakdown by day
        $dataSourceByDay = MonitoringData::query()
            ->whereBetween('monitoring_data.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->selectRaw('DATE(monitoring_data.created_at) as date, data_source, COUNT(*) as count')
            ->groupBy('date', 'data_source')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Format data source breakdown for chart
        $dataSourceChart = [];
        foreach ($dateRange as $day) {
            $dayStats = $dataSourceByDay->get($day['date'], collect());
            $online = $dayStats->where('data_source', 'online')->sum('count');
            $offline = $dayStats->where('data_source', 'offline')->sum('count');

            $dataSourceChart[] = [
                'date' => $day['formatted_date'],
                'online' => $online,
                'offline' => $offline
            ];
        }

        // Get filter options
        $provinsiList = Provinsi::select('id', 'nama')->orderBy('nama')->get();
        $userList = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('UserPerformance/Index', [
            'chartData' => $dateRange,
            'dataSourceChart' => $dataSourceChart,
            'userStats' => $userStats,
            'provinceStats' => $provinceStats,
            'statistics' => [
                'total_posts' => $totalPosts,
                'online_posts' => $onlinePosts,
                'offline_posts' => $offlinePosts,
                'unique_users' => $uniqueUsers,
                'online_percentage' => $totalPosts > 0 ? round(($onlinePosts / $totalPosts) * 100, 1) : 0,
                'offline_percentage' => $totalPosts > 0 ? round(($offlinePosts / $totalPosts) * 100, 1) : 0,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'provinsi_id' => $provinsiId,
                'user_id' => $userId,
                'data_source' => $dataSource,
            ],
            'filterOptions' => [
                'provinsiList' => $provinsiList,
                'userList' => $userList,
            ],
        ]);
    }
}