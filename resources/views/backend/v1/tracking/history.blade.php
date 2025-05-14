@extends('layouts.backend.v1.main')

@section('breadcrumb')
    @component('layouts.backend.v1.components.breadcrumb', [
        'title' => 'Tracking History',
        'breadcrumbs' => [
            ['title' => 'Home', 'url' => route('dashboard')],
            ['title' => 'Tracking History', 'active' => true],
        ],
    ])
    @endcomponent
@endsection

@section('content')

@endsection