@extends('base')



@section('content')

@if(session('lti_notice'))
<div class="alert alert-success">
            {!! session('lti_notice') !!}
        </div>
@endif

@if(session('lti_error'))
<div class="alert alert-danger m-3">
           <strong>Notice: </strong> {!! session('lti_error') !!}
        </div>
@endif

<router-view :user="{{$user}}"></router-view>
{{-- <Home class="container" :user="{{$user}}"></Home> --}}
@endsection
