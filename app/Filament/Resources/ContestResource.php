<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContestResource\Pages;
use App\Filament\Resources\ContestResource\RelationManagers;
use App\Models\Contest;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContestResource extends Resource
{
    protected static ?string $model = Contest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Form fields for creating and updating records.
     *
     * @param Form $form Form object for the resource.
     *
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Select::make('entry_description_display_type')
                    ->label('Entry Description Display')
                    ->options([
                        'hidden'  => 'Hidden',
                        'tooltip' => 'Tooltip',
                        'inline'  => 'Inline',
                    ])
                    ->default('hidden')
                    ->required()
                    ->columnSpan(1),
                Forms\Components\Placeholder::make('spacer')
                    ->label('')
                    ->columnSpan(1),
                Forms\Components\TextInput::make('rating_max')
                    ->label('Max Rating')
                    ->numeric()
                    ->default(5)
                    ->columnSpan(1),
                Forms\Components\Placeholder::make('spacer')
                    ->label('')
                    ->columnSpan(1),
                Forms\Components\DateTimePicker::make('voting_window_opens_at')
                    ->label('Voting Window Opens At')
                    ->seconds(false)
                    ->native(false)
                    ->timezone(
                        auth()->user()->timezone ?? config('app.timezone')
                    )
                    ->rules(function (Forms\Get $get) {
                        return [
                            'nullable',
                            'date',
                            function (string $attribute, $value, Closure $fail) use ($get) {
                                $opensAt  = Carbon::parse($value)->utc();
                                $closesAt = Carbon::parse($get('voting_window_closes_at'))->utc();

                                if (
                                    !empty($value)
                                    && !empty($get('voting_window_closes_at'))
                                    && $opensAt->greaterThan($closesAt)
                                ) {
                                    $fail('The Voting Window Opens At must be before Voting Window Closes At.');
                                }
                            },
                        ];
                    })
                    ->columnSpan(1),
                Forms\Components\DateTimePicker::make('voting_window_closes_at')
                    ->label('Voting Window Closes At')
                    ->seconds(false)
                    ->native(false)
                    ->timezone(
                        auth()->user()->timezone ?? config('app.timezone')
                    )
                    ->rules(function (Forms\Get $get) {
                        return [
                            'nullable',
                            'date',
                            function (string $attribute, $value, \Closure $fail) use ($get) {
                                $closesAt = Carbon::parse($value)->utc();
                                $opensAt  = Carbon::parse($get('voting_window_opens_at'))->utc();

                                if (
                                    !empty($value)
                                    && !empty($get('voting_window_opens_at'))
                                    && $closesAt->lessThan($opensAt)
                                ) {
                                    $fail('The Voting Window Closes At must be after Voting Window Opens At.');
                                }
                            },
                        ];
                    })
                    ->columnSpan(1),
            ]);
    }

    /**
     * Table columns and filters.
     *
     * @param Table $table Table object for the resource.
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->tooltip(fn ($record) => $record->description)
                    ->limit(30)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('voting_window_opens_at')
                    ->label('Voting Opens')
                    ->datetime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('voting_window_closes_at')
                    ->label('Voting Closes')
                    ->datetime()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_voting_open')
                    ->label('Is Voting Open?')
                    ->getStateUsing(fn ($record) => $record->isVotingOpen())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->tooltip(function ($record) {
                        $tooltip = $record->votingWindowTooltip;
                        return $tooltip;
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Returns relation manager relations
     *
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\EntriesRelationManager::class,
            RelationManagers\RatingFactorsRelationManager::class,
        ];
    }

    /**
     * Returns the pages available for the resource
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContests::route('/'),
            'create' => Pages\CreateContest::route('/create'),
            'edit'   => Pages\EditContest::route('/{record}/edit'),
        ];
    }
}
