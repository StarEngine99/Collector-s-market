@extends('layouts.admin')

@section('title', 'Thêm banner - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Thêm banner trang chủ</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.banners._form')
            </form>
        </div>
    </div>
@endsection
