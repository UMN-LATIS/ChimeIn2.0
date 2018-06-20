@extends('base')

@section('content')
<router-view :user="{{$user}}"></router-view>
@endsection
