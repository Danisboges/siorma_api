<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Ormawa;
use Illuminate\Http\Request;

class PostApiController extends BaseApiController
{
    /**
     * Tambahkan poster_url agar FE bisa langsung menampilkan gambar
     */
    private function withPosterUrl($post)
    {
        if (!$post) return $post;

        $path = $post->posterPath ?? null;

        $post->poster_url = $path
            ? asset('storage/' . ltrim(str_replace('public/', '', $path), '/'))
            : null;

        return $post;
    }

    /**
     * GET /api/posts
     * List post/event untuk user (published saja)
     */
    public function index()
    {
        $posts = Post::with(['user', 'ormawa'])
            ->where('status', 'published')
            ->latest('created_at')
            ->get();

        $posts->transform(function ($p) {
            return $this->withPosterUrl($p);
        });

        return $this->success($posts, 'Daftar posts (published)');
    }

    /**
     * GET /api/posts/{postID}
     * Detail post/event (untuk FE)
     */
    public function show($postID)
    {
        $post = Post::with(['user', 'ormawa'])
            ->where('postID', $postID)
            ->first();

        if (! $post) {
            return $this->error('Post tidak ditemukan', 404);
        }

        $post = $this->withPosterUrl($post);

        return $this->success($post, 'Detail post');
    }

    /**
     * GET /api/admin/posts
     * List semua post untuk admin
     */
    public function adminIndex(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $posts = Post::with(['user', 'ormawa'])
            ->latest('created_at')
            ->get();

        $posts->transform(function ($p) {
            return $this->withPosterUrl($p);
        });

        return $this->success($posts, 'Daftar semua posts (admin)');
    }

    /**
     * POST /api/admin/posts
     * Create post baru
     * Field sesuai model Post:
     * - ormawaID, userID, title, description, posterPath, status
     */
    public function store(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return $this->error('Akses hanya untuk admin.', 403);
    }

    $validated = $request->validate([
        'ormawaID'    => 'required|exists:ormawa,id',
        'title'       => 'required|string|max:255',
        'description' => 'required|string',
        'status'      => 'required|in:draft,published',
        'poster'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $validated['userID'] = $request->user()->id;

    // ⬇️ INI KUNCI UTAMA
    if ($request->hasFile('poster')) {
        $path = $request->file('poster')->store('posters', 'public');
        $validated['posterPath'] = $path;
    }

    $post = Post::create($validated);
    $post->load(['user', 'ormawa']);
    $post = $this->withPosterUrl($post);

    return $this->success($post, 'Post berhasil dibuat', 201);
}


    /**
     * PUT /api/admin/posts/{postID}
     * Update post (admin)
     */
    public function update(Request $request, $postID)
    {
        if (! $request->user()->isAdmin()) {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $post = Post::where('postID', $postID)->first();
        if (! $post) {
            return $this->error('Post tidak ditemukan', 404);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status'      => 'sometimes|required|in:draft,published',
            'poster'      => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('poster')) {
            $path = $request->file('poster')->store('posters', 'public');
            $validated['posterPath'] = $path;
        }

        $post->update($validated);
        $post->load(['user', 'ormawa']);
        $post = $this->withPosterUrl($post);

        return $this->success($post, 'Post berhasil diperbarui');
    }

    /**
     * DELETE /api/admin/posts/{postID}
     * Hapus post (admin)
     */
    public function destroy(Request $request, $postID)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $post = Post::where('postID', $postID)->first();

        if (! $post) {
            return $this->error('Post tidak ditemukan', 404);
        }

        $post->delete();

        return $this->success(null, 'Post berhasil dihapus');
    }
}
