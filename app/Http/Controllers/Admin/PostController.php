<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Services\PostManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller quản lý bài viết (B6).
 */
class PostController extends Controller
{
    public function __construct(
        private readonly PostManagementService $postService
    ) {
    }

    public function index(Request $request): View
    {
        $posts = $this->postService->paginatePosts(
            $request->query('q'),
            $request->query('status'),
            15
        );

        return view('admin.posts.index', [
            'posts' => $posts,
            'keyword' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $this->postService->create(
            $request->validated(),
            $request->user()?->getKey()
        );

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Đã tạo bài viết mới thành công.');
    }

    public function edit(int $id): View
    {
        $post = $this->postService->findById($id);
        abort_if($post === null, 404);

        return view('admin.posts.edit', ['post' => $post]);
    }

    public function update(PostRequest $request, int $id): RedirectResponse
    {
        $post = $this->postService->findById($id);
        abort_if($post === null, 404);

        $this->postService->update($id, $request->validated());

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $deleted = $this->postService->delete($id);
        abort_if(!$deleted, 404);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Đã xóa bài viết.');
    }
}
