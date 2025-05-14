@extends('layouts.backend.v1.main')

@section('breadcrumb')
    @component('layouts.backend.v1.components.breadcrumb', [
        'title' => 'Order',
        'breadcrumbs' => [
            ['title' => 'Home', 'url' => route('dashboard')],
            ['title' => 'Order', 'active' => true],
        ],
    ])
    @endcomponent
@endsection

@section('content')

@endsection