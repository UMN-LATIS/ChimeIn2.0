@extends('base')


@section('header')
<script>
	window.chime = {{ $chime->id }};
</script>
@endsection
@section('content')

<router-view :user="{{$user}}"></router-view>
@endsection
