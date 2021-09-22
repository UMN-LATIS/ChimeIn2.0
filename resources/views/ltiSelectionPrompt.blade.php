@extends('base')

@section('header')


@endsection

@section('content')

<div class="container">
    {{ Form::model($chime, array('route'=> ['ltisettings.update', $chime->id], 'method'=> 'put')) }}
    {{ Form::hidden("lti_resource_title", $lti_resource_title) }}
    {{ Form::hidden("resource_link_pk", $resource_link_pk) }}
    <lti-launch :lti_details='@json($ltiLaunch)'></lti-launch>
</div>
@endsection