@extends('base')

@section('content')
<navbar
    :title="'Home'"
    :user="{{$user}}"
    :link="'/'">
</navbar>
<Home :user="{{$user}}"></Home>
@endsection
