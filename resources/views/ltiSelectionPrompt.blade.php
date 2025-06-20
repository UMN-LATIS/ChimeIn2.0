@extends('base')

@section('header')

@endsection

@section('content')

<default-layout :user="{{Auth::user()}}">
<div class="container-fluid">
    <form method="POST" action="{{ route($saveTarget, $chime->id) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="lti_resource_title" value="{{ $lti_resource_title }}">
        <input type="hidden" name="resource_link_pk" value="{{ $resource_link_pk }}">
        <lti-launch :lti_details='@json($ltiLaunch)'></lti-launch>
    </form>
</div>   
</default-layout>
@endsection

@section('footer')

@endsection