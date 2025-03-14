@extends('layouts.brifapp')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            @include('common/module/create')
        </div>
    </div>
</div>
@endsection
