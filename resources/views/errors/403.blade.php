@extends('layouts.app')

@section('content')
    <div id="login-container" class="container-fluid" style="background-color: lightgray;">
        <div class="row">
            <div class="col-md-12 mt-1 mb-1 text-center">
                <h1>Error 403, You don't have permissions to access this page !!!</h1>
                <a href="{{ asset('/') }}">back to home</a>
            </div>
        </div>
    </div>
@endsection