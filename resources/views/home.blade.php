@extends('base')



@section('content')

@if(session('created_grade_entry'))
<div class="alert alert-success">
            {!! session('created_grade_entry') !!}
        </div>
@endif



<router-view :user="{{$user}}"></router-view>
{{-- <Home class="container" :user="{{$user}}"></Home> --}}
@endsection
