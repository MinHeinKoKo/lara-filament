<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Department;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Illuminate\Support\Collection;
use Filament\Forms\Set;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static ?string $navigationGroup = "Employee Management";


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make("User Name")
            ->description("Put User infromation in here.")
            ->schema([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('middle_name')
                    ->required()
                    ->maxLength(255),
            ])->columns(3),

                Section::make("User Address")
            ->description("Put User address in here.")
            ->schema([
                TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                TextInput::make('zip_code')
                    ->required()
                    ->maxLength(255),
            ])->columns(2),
                Section::make("RelationShip")
                ->schema([
                    Select::make('country_id')
                        ->relationship('country','name')
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->afterStateUpdated(function (Set $set){
                            $set('state_id', null);
                            $set('city_id', null);
                    })
                        ->live()
                        ->required(),
                    Select::make('state_id')
                        ->options(fn (Get $get): Collection => State::query()
                        ->where('country_id',$get('country_id'))
                        ->pluck('name','id'))
                        ->native(false)
                        ->searchable()
                        ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                        ->live()
                        ->preload()
                        ->required(),
                    Select::make('city_id')
                        ->options(fn (Get $get): Collection => City::query()
                            ->where('state_id',$get('state_id'))
                            ->pluck('name','id'))
                        ->native(false)
                        ->searchable()
                        ->live()
                        ->preload()
                        ->required(),
                    Select::make('department_id')
                        ->relationship('department','name')
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->required(),
                ])->columns(2),
                Section::make("User date")
                    ->description("Put date information in here.")
                    ->schema([
                        DatePicker::make('date_of_birth')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('date_of_hired')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                        ->required()
//                        ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('country.name')
                    ->label("Country")
                    ->sortable(),
                Tables\Columns\TextColumn::make('state.name')
                    ->label("State")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label("City")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label("Department")
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_of_hired')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('Department')
                ->relationship("department",'name')
                ->searchable()
                ->preload()
                ->label("Filter By Department")
                ->indicator('Department')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            \Filament\Infolists\Components\Section::make("Employee Information")
                ->schema([
                    \Filament\Infolists\Components\Section::make("RelationShip")
                    ->schema([
                        TextEntry::make('country.name')->label('Country Name'),
                        TextEntry::make('state.name')->label('State Name'),
                        TextEntry::make('city.name')->label('City Name'),
                        TextEntry::make('department.name')->label('Department Name'),
                    ])->columns(2),
                    \Filament\Infolists\Components\Section::make("Employee's Information")
                        ->schema([
                            TextEntry::make('first_name')->label('First Name'),
                            TextEntry::make('middle_name')->label('Middle Name'),
                            TextEntry::make('last_name')->label('Last Name'),
                        ])->columns(3),
                    \Filament\Infolists\Components\Section::make("Employee's Address")
                        ->schema([
                            TextEntry::make('address')->label('Address'),
                            TextEntry::make('zip_code')->label('Zip Code'),
                        ])->columns(2),
                ])
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
//            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
