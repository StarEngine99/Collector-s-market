@extends('layouts.admin')

@section('title', 'Chỉnh sửa bài viết - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Chỉnh sửa bài viết #{{ $post->MaBaiViet }}</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.posts.update', $post->MaBaiViet) }}">
                @csrf
                @method('PUT')
                @include('admin.posts._form', ['post' => $post])
            </form>
        </div>
    </div>
@endsection
