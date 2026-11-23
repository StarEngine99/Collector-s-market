@extends('layouts.admin')

@section('title', 'Thêm sản phẩm - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Thêm sản phẩm</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.products._form')
            </form>
        </div>
    </div>
@endsection
