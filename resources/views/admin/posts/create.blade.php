@extends('layouts.admin')

@section('title', 'Thêm bài viết - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Thêm bài viết mới</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.posts.store') }}">
                @csrf
                @include('admin.posts._form')
            </form>
        </div>
    </div>
@endsection
