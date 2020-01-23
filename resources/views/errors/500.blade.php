@extends('base')
@section('content')

<div class="alert alert-danger" role="alert">
    <strong>Error: </strong> {!! $exception->getMessage() !!}
</div>

@endsection