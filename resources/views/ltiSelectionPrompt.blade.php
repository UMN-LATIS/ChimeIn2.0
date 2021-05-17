@extends('base')



@section('header')

<style>
.checkbox_options {
    color: hsla(215, 5%, 50%, 1);
}
.checkbox_options input[type="radio"] {
  display: none;
}
.checkbox_options input[type="radio"]:not(:disabled) ~ label {
    cursor: pointer;
  }
.checkbox_options input[type="radio"]:disabled ~ label {
    color: hsla(150, 5%, 75%, 1);
    border-color: hsla(150, 5%, 75%, 1);
    box-shadow: none;
    cursor: not-allowed;
}
.checkbox_options  label {
  height: 100%;
  display: block;
  background: white;
  border: 2px solid #17a2b8;
  border-radius: 20px;
  padding: 1.3rem;
  margin-bottom: 1rem;
  text-align: left;
  box-shadow: 0px 3px 10px -2px hsla(150, 5%, 65%, 0.5);
  position: relative;
}
.checkbox_options  input[type="radio"]:checked + label {
  background:  #17a2b8;
  color: hsla(215, 0%, 100%, 1);
  box-shadow: 0px 0px 20px #17a2b8;
}
.checkbox_options  input[type="radio"]:checked + label::after {
    color: hsla(215, 5%, 25%, 1);
    font-family: 'Material Icons';
    border: 2px solid #17a2b8;
    content: "\e876";
    font-size: 24px;
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    height: 50px;
    width: 50px;
    line-height: 50px;
    text-align: center;
    border-radius: 50%;
    background: white;
    box-shadow: 0px 2px 5px -2px hsla(0, 0%, 0%, 0.25);

}

@media only screen and (max-width: 700px) {
  .checkbox_options  {
    flex-direction: column;
  }
}

.checkbox_options  {
  display: flex;
  flex-flow: row wrap;
}
.checkbox_options  > div {
  flex: 1;
  padding: 0.5rem;
}

.hr-sect {
    display: flex;
    flex-basis: 100%;
    align-items: center;
    color: rgba(0, 0, 0, 0.75);
    margin: 8px 0px;
}
.hr-sect:before,
.hr-sect:after {
    content: "";
    flex-grow: 1;
    background: rgba(0, 0, 0, 0.35);
    height: 1px;
    font-size: 0px;
    line-height: 0px;
    margin: 0px 8px;
}

</style>
@endsection

@section('content')



<div class="container">
<div class="row mt-2">
    <div class="col-12">
        <h1>Canvas/ChimeIn Setup</h1>
        <p>How would you like to use ChimeIn with this course? If you're not sure about these options, visit our <a href="https://umn-latis.github.io/ChimeIn2.0/canvas.html" target="_blank">help page</a>.</p>

        {{ Form::model($chime, array('route' => ['ltisettings.update', $chime->id],  'method' => 'put')) }}
        {{ Form::hidden("lti_resource_title", $lti_resource_title)}}
        <div class="checkbox_options">
            
                <div>
                    {{ Form::radio('lti_grade_mode', \App\LTI13ResourceLink::LTI_GRADE_MODE_NO_GRADES, false, ['class'=>'', 'id'=>'no_chime']) }}
                     <label for="no_chime"> 
                         <h2>No Grades</h2>
                         <p>ChimeIn won't pass any data back to Canvas. If needed, you can still access participation data within ChimeIn</p>
                     </label>
                </div>
                <div>
                        {{ Form::radio('lti_grade_mode', \App\LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES, false, ['class'=>'', 'id'=>'multiple_gradebook',  $haveLineItem?null:"disabled"]) }}
                        <label for="multiple_gradebook">
                            <h2>Multiple gradebook entries </h2>
                         <p>This will allow you to seperately track grades for each week/lecture/etc. You'll create multiple assignments in Canvas, and manage your points in Canvas.</p>
                         @if(!$haveLineItem)
                        <b style="color:black">You must create an assignment in Canvas to select this option</b>    
                    @endif   
                    </label>
                </div>
                <div>
                        {{ Form::radio('lti_grade_mode', \App\LTI13ResourceLink::LTI_GRADE_MODE_ONE_GRADE, true, ['class'=>'', 'id'=>'one_gradebook']) }}
                        <label for="one_gradebook">
                        <h2>One gradebook entry</h2>
                        <p>
                            The Canvas gradebook entry will be based on the total number of questions students respond to across all of your folders. You can still create folders in ChimeIn to organize your questions. 
                        </p>
                         
                    </label>
                    
                </div>
        </div>

        <h2 class="hr-sect">Grading Calculation</h2>
        <div class="checkbox_options">

                <div>
                        {{ Form::radio('only_correct_answers_lti', 0, true, ['class'=>'', "id"=>"any_participation"]) }}
                    <label for="any_participation">
                        <h2>Any participation</h2>
                        <p>
                            Any response counts towards the grade in the gradebook.
                        </p>
                    </label>
                </div>
                <div>
                        {{ Form::radio('only_correct_answers_lti', 1, null, ['class'=>'', "id"=>"only_correct"]) }}
                    <label for="only_correct">
                        <h2>"Correct" answers</h2>
                        <p>
Multiple Choice questions in ChimeIn can have a "correct" answer assigned. When this option is selected, students responding to multiple choice questions will only recieve credit for correct answers. Any participation in other types of questions (free response, heatmap, etc) counts towards the grade..
                        </p>
                    </label>
                </div>
                 <div>
                    
                        {{ Form::radio('only_correct_answers_lti', 2, null, ['class'=>'', "id"=>"partial_credit"]) }}
                        <label for="partial_credit" >
                            <h2>Partial credit</h2>
                        <p>Half credit for participation, full credit for "correct" answers<p>
                        
                    </label>
                </div>
            </div>

				<div class="col-sm-12 text-center mt-2">
					{{ Form::submit('Save Canvas/ChimeIn Settings', array('class' => 'btn btn-primary ')) }}
				</div>
        {{ Form::close() }}

    </div>
</div>

</div>


@endsection