@extends('layouts.admin')

@section('title', 'Chỉnh sửa banner - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Chỉnh sửa banner #{{ $banner->MaBanner }}</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.banners.update', $banner->MaBanner) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.banners._form', ['banner' => $banner])
            </form>
        </div>
    </div>
@endsection
