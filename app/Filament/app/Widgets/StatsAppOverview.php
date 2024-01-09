<?php

namespace App\Filament\App\Widgets;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsAppOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
//            Stat::make('Unique views', '192.1k'),
//            Stat::make('Bounce rate', '21%'),
//            Stat::make('Average time on page', '3:12'),
//            Stat::make('Unique views', '192.1k')
//                ->description('32k increase')
//                ->descriptionIcon('heroicon-m-arrow-trending-up'),
//            Stat::make('Bounce rate', '21%')
//                ->description('7% increase')
//                ->descriptionIcon('heroicon-m-arrow-trending-down'),
//            Stat::make('Average time on page', '3:12')
//                ->description('3% increase')
//                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Users', Team::find(Filament::getTenant())->first()->members()->count())
                ->description('All Users from the database')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Departments', Department::query()->whereBelongsTo(Filament::getTenant())->count())
                ->description('All Departments from the database')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Employees', Employee::query()->whereBelongsTo(Filament::getTenant())->count())
                ->description('All Employees from the database')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
