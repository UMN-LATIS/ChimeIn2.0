@extends('base')



@section('content')
<div class="container">
<div class="row">
    <div class="col-12">
        <h1>Canvas/ChimeIn Setup</h1>
        <p>How would you like to use ChimeIn with this course? If you're not sure about these options, visit our <a href="https://umn-latis.github.io/ChimeIn2.0/canvas.html" target="_blank">help page</a>.</p>

        {{ Form::model($chime, array('route' => ['ltisettings.update', $chime->id],  'method' => 'put')) }}

        <div class="form-group">
            <div class="col-sm-8">

                <div class="radio">
                    <label>
                        {{ Form::radio('single_chime_for_lti', 1, true, ['class'=>'']) }}
                        I don't want ChimeIn results in the Canvas gradebook
                        
                    </label>
                </div>
                <div class="radio">
                    <label>
                        {{ Form::radio('single_chime_for_lti', 0, true, ['class'=>'']) }}
                        Individual gradebook entry for each lecture/week
                        
                    </label>
                </div>
                <div class="radio">
                    <label>
                        {{ Form::radio('single_chime_for_lti', 1, false, ['class'=>'']) }}
                        One gradebook entry for the whole semester
                        <small id="passwordHelpBlock" class="form-text">
                            The Canvas gradebook entry will be based on the total number of questions students respond to across all of your folders. You can still create folders in ChimeIn to organize your questions. 
                        </small>
                    </label>
                    
                </div>
            </div>
        </div>
        <hr>
        <div class="form-group">
            {{ Form::label('only_correct_answers_lti', '<h2>Grading Calculation</h2>', ['class'=>'col-sm-8 control-label'], false) }}
            <div class="col-sm-8">

                <div class="radio">
                    <label>
                        {{ Form::radio('only_correct_answers_lti', 0, true, ['class'=>'']) }}
                        Count any participation
                        <small id="passwordHelpBlock" class="form-text text-muted">
                            Any response counts towards the grade in the gradebook.
                        </small>
                    </label>
                </div>
                <div class="radio">
                    <label>
                        {{ Form::radio('only_correct_answers_lti', 1, null, ['class'=>'']) }}
                        Only count "correct" answers
                        <small id="passwordHelpBlock" class="form-text text-muted">
Multiple Choice questions in ChimeIn can have a "correct" answer assigned. When this option is selected, students responding to multiple choice questions will only recieve credit for correct answers. Any participation in other types of questions (free response, heatmap, etc) counts towards the grade..
</small>
                    </label>
                </div>
            </div>
        </div>
            {{Form::hidden("resource_link_title", $resource_link_title)}}
            {{Form::hidden("resource_link_pk", $resource_link_pk)}}

				<div class="col-sm-12 text-center">
					{{ Form::submit('Save', array('class' => 'btn btn-primary ')) }}
				</div>
        {{ Form::close() }}

    </div>
</div>

</div>
@endsection