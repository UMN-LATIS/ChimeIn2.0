@extends('base')
@section('content')

<div class="container">
    <div class="alert alert-danger" role="alert">
        <strong>Error: </strong> {!! $exception->getMessage() !!}
    </div>
</div>

@endsection