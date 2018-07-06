@extends('base')

@section('header')
<script>
	window.chime = {{ $chime->id }};
</script>
@endsection

@section('content')
<navbar
    :title="'Back to Home'"
    :user="{{$user}}"
    :link="'/'">
</navbar>

<Chime class="container" :user="{{$user}}"></Chime>
@endsection
