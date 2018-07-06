@extends('base')



@section('content')
<navbar
    :title="'Home'"
    :user="{{$user}}"
    :link="'/'">
</navbar>
<Home class="container" :user="{{$user}}"></Home>
@endsection
