@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @include('admin.sidebar')

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">User {{ $user->id }}</div>
                <div class="card-body">
                    @canImpersonate
                        <a href="{{ route('impersonate', $user->id) }}">Impersonate this user</a>
                    @endCanImpersonate
                    <a href="{{ url('/admin/users') }}" title="Back"><button class="btn btn-warning btn-sm"><i
                                class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>
                    <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" title="Edit User"><button
                            class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            Edit</button></a>

                    <form method="POST" action="{{ url('admin/users' . '/' . $user->id) }}" accept-charset="UTF-8"
                        style="display:inline">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-danger btn-sm" title="Delete User"
                            onclick="return confirm(&quot;Confirm delete?&quot;)"><i class="fa fa-trash-o"
                                aria-hidden="true"></i> Delete</button>
                    </form>
                    <br />
                    <br />

                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <th> Name </th>
                                    <td> {{ $user->name }} </td>
                                </tr>
                                <tr>
                                    <th> Email </th>
                                    <td> {{ $user->email }} </td>
                                </tr>
                                <tr>
                                    <th> Permission Number </th>
                                    <td> {{ $user->permission_number }} </td>
                                </tr>
                                <tr>
                                    <th> Global Admin </th>
                                    <td> {{ $user->global_admin }} </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection