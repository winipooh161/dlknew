@extends('layouts.app')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            @include('deals/deal_change_logs')
        </div>
    </div>
</div>
@endsection
