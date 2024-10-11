<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\User>
     */
    public static $model = \App\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'email';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email', 'umndid',
    ];

    public static $perPageViaRelationship = 50;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Gravatar::make()->maxWidth(50),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make('UMNDID')
                ->sortable()
                ->filterable()
                ->rules('required', 'max:255'),

            Boolean::make('Guest User')
                ->filterable()
                ->sortable(),

            Boolean::make('Global Admin')
                ->filterable()
                ->sortable(),

            BelongsToMany::make('Chimes')->fields(function () {
                return [
                    Select::make('Permission Number')->options([
                        100 => 'Participant',
                        300 => 'Presenter',
                    ])->displayUsingLabels(),
                ];
            }),

            MorphMany::make('Auths', 'authentications', AuthenticationLog::class),

            DateTime::make('Created At')->sortable()->onlyOnDetail(),
            DateTime::make('Updated At')->sortable()->onlyOnDetail(),
            DateTime::make('Last Login At', function () {
                return $this->lastLoginAt();
            })->sortable()->onlyOnDetail(),
            Text::make('Last Login IP', function () {
                return $this->lastLoginIP();
            })->sortable()->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
