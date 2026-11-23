@extends('layouts.admin')

@section('title', 'Thêm chương trình khuyến mãi - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Thêm chương trình khuyến mãi theo sản phẩm</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.promotions.store') }}">
                @csrf
                @include('admin.promotions._form')
            </form>
        </div>
    </div>
@endsection
