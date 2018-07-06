@extends('base')
@section('header')
<script>
	window.chime = {{ $chime->id }};
</script>
@endsection
@section('content')
<ChimeStudent class="container" :user="{{$user}}"></ChimeStudent>
@endsection
