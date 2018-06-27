@extends('base')
@section('header')
<script>
	window.chime = {{ $chime->id }};
</script>
@endsection
@section('content')
<ChimeStudent :user="{{$user}}"></ChimeStudent>
@endsection
