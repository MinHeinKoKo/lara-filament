<?php

namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Models\City;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public function form(Form $form): Form
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('first_name'),
                Tables\Columns\TextColumn::make('middle_name'),
                Tables\Columns\TextColumn::make('last_name'),
                Tables\Columns\TextColumn::make('department.name'),
                Tables\Columns\TextColumn::make('zip_code'),
                Tables\Columns\TextColumn::make('date_of_hired')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
