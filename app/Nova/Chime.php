<?php

namespace App\Nova;

use App\Constants\LTIGradeMode;
use App\Constants\LTIGradeOptions;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Chime extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Chime>
     */
    public static $model = \App\Chime::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Url::make('Access Code', fn () => "/join/{$this->access_code}"
            )
                ->sortable()
                ->rules('required', 'max:255')
                ->displayUsing(fn () => $this->access_code),

            Text::make('Name')->sortable()->rules('required', 'max:255'),

            Number::make('Presenters', function () {
                return $this->presenters()->count();
            })->onlyOnIndex(),

            Number::make('Participants', function () {
                return $this->participants()->count();
            })->onlyOnIndex(),

            Boolean::make('Canvas Chime', 'lti_return_url'),

            Panel::make('Chime Settings', $this->chimeSettingsPanel()),

            Panel::make('LTI Configuration', $this->ltiConfigurationPanel()),

            BelongsToMany::make('Users')->fields(function () {
                return [
                    Select::make('Role', 'permission_number')->options([
                        100 => 'Participant',
                        300 => 'Presenter',
                    ])->displayUsingLabels()->sortable()->filterable(),
                ];
            }),
        ];
    }

    public function chimeSettingsPanel()
    {
        return [

            Boolean::make('Require Login')->onlyOnDetail(),

            Boolean::make('Participants Can View Results', 'students_can_view')->onlyOnDetail(),

            Boolean::make('Show Folder Title to Participants')
                ->onlyOnDetail(),

            Boolean::make('Show Join Instructions', 'join_instructions')->onlyOnDetail(),
        ];
    }

    public function ltiConfigurationPanel()
    {
        return [
            URL::make('LTI Return URL')
                ->displayUsing(fn () => $this->lti_return_url)
                ->onlyOnDetail(),

            Text::make('LTI Course Title')->sortable()->filterable()->onlyOnDetail(),

            Text::make('LTI Course ID')->onlyOnDetail(),

            Select::make('LTI Credit for Incorrect', 'only_correct_answers_lti')
                ->options([
                    LTIGradeOptions::FULL_CREDIT_FOR_INCORRECT => '100%',
                    LTIGradeOptions::HALF_CREDIT_FOR_INCORRECT => '50%',
                    LTIGradeOptions::NO_CREDIT_FOR_INCORRECT => '0%',
                ])
                ->displayUsingLabels()
                ->onlyOnDetail(),

            Select::make('LTI Grade Mode', 'lti_grade_mode')
                ->options([
                    LTIGradeMode::NO_GRADES => 'No Grades',
                    LTIGradeMode::ONE_GRADE => 'One Grade',
                    LTIGradeMode::MULTIPLE_GRADES => 'Multiple Grades',
                ])
                ->displayUsingLabels()
                ->onlyOnDetail(),

            Boolean::make('LTI Setup Complete')
                ->onlyOnDetail(),

            BelongsTo::make('LTI13 Resource Link', 'lti13_resource_link', LTI13ResourceLink::class)
                ->sortable()->nullable()->onlyOnDetail(),

            Text::make('LTI Resource Link PK (deprecated)')
                ->onlyOnDetail(),
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
