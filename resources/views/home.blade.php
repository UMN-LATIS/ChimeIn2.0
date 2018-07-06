@extends('base')



@section('content')
<router-view :user="{{$user}}"></router-view>
{{-- <Home class="container" :user="{{$user}}"></Home> --}}
@endsection
