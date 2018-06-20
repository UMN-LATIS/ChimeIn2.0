@extends('base')

@section('content')
<navbar
    :title="'Back to Home'"
    :user="{{$user}}"
    :link="'/'">
</navbar>
<Chime :user="{{$user}}"></Chime>
@endsection
