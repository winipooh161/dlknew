@extends('layouts.app')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel " id="step-2">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            @include('module/home')
        </div>
    </div>
</div>
@endsection
