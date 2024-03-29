@extends('base')

@section('header')

@endsection

@section('content')

<default-layout :user="{{Auth::user()}}">
<div class="container-fluid">
    {{ Form::model($chime, array('route'=> [$saveTarget, $chime->id], 'method'=> 'put')) }}
    {{ Form::hidden("lti_resource_title", $lti_resource_title) }}
    {{ Form::hidden("resource_link_pk", $resource_link_pk) }}
    <lti-launch :lti_details='@json($ltiLaunch)'></lti-launch>
</div>   
</default-layout>
@endsection

@section('footer')

@endsection