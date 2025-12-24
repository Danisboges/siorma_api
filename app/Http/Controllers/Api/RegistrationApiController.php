<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegistrationApiController extends BaseApiController
{
    /**
     * POST /api/posts/{postID}/registrations
     * User mendaftar ke event/post tertentu
     * Butuh auth (di group auth:sanctum)
     */
    public function store(Request $request, $postID)
    {
        $user = $request->user();

        $post = Post::where('postID', $postID)
            ->where('status', 'published')
            ->first();

        if (! $post) {
            return $this->error('Event / post tidak ditemukan atau belum dipublish.', 404);
        }

        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'nim'          => 'required|string|max:50',
            'email'        => 'required|email|max:255',
            'phone'        => 'required|string|max:20',
            'organization' => 'nullable|string|max:255',
            'reason'       => 'nullable|string',
            'cv'           => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        // Cek apakah sudah pernah daftar (opsional, tapi biasanya penting)
        $existing = Registration::where('user_id', $user->id)
            ->where('post_id', $post->postID)
            ->first();

        if ($existing) {
            return $this->error('Anda sudah terdaftar pada event ini.', 409);
        }

        $cvPath = null;

        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cv', 'public');
        }

        $registration = Registration::create([
            'user_id'      => $user->id,
            'post_id'      => $post->postID,
            'full_name'    => $validated['full_name'],
            'nim'          => $validated['nim'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'],
            'organization' => $validated['organization'] ?? null,
            'reason'       => $validated['reason'] ?? null,
            'status'       => 'pending',
            'cv_path'      => $cvPath,
        ]);

        $registration->load(['user', 'post']);

        return $this->success($registration, 'Pendaftaran berhasil dikirim', 201);
    }

    /**
     * GET /api/admin/registrations
     * Versi API dari adminIndex() (lihat semua pendaftaran)
     */
    public function adminIndex(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $registrations = Registration::with(['user', 'post'])
            ->latest('created_at')
            ->get();

        return $this->success($registrations, 'Daftar semua pendaftaran');
    }

    /**
     * PATCH /api/admin/registrations/{id}/status
     * Versi API dari updateStatus()
     */
    public function updateStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $registration = Registration::find($id);

        if (! $registration) {
            return $this->error('Pendaftaran tidak ditemukan.', 404);
        }

        $registration->status = $validated['status'];
        $registration->save();

        return $this->success($registration, 'Status pendaftaran berhasil diperbarui');
    }

    /**
     * DELETE /api/admin/registrations/{id}
     * Versi API dari destroy()
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $registration = Registration::find($id);

        if (! $registration) {
            return $this->error('Pendaftaran tidak ditemukan.', 404);
        }

        $registration->delete();

        return $this->success(null, 'Pendaftaran berhasil dihapus');
    }
    public function adminViewCv(Request $request, $id)
{
    if ($request->user()->role !== 'admin') {
        return $this->error('Akses hanya untuk admin.', 403);
    }

    $reg = Registration::find($id);
    if (! $reg) {
        return $this->error('Registration tidak ditemukan', 404);
    }

    if (! $reg->cv_path) {
        return $this->error('CV belum diupload', 404);
    }

    $disk = Storage::disk('public');

    if (! $disk->exists($reg->cv_path)) {
        return $this->error('File CV tidak ditemukan di storage', 404);
    }

    $fullPath = $disk->path($reg->cv_path);
    return response()->file($fullPath);
}

public function adminDownloadCv(Request $request, $id)
{
    if ($request->user()->role !== 'admin') {
        return $this->error('Akses hanya untuk admin.', 403);
    }

    $reg = Registration::find($id);
    if (! $reg) {
        return $this->error('Registration tidak ditemukan', 404);
    }

    if (! $reg->cv_path) {
        return $this->error('CV belum diupload', 404);
    }

    $disk = Storage::disk('public');

    if (! $disk->exists($reg->cv_path)) {
        return $this->error('File CV tidak ditemukan di storage', 404);
    }

    $fullPath = $disk->path($reg->cv_path);
    $ext = pathinfo($reg->cv_path, PATHINFO_EXTENSION);
    $filename = 'CV_' . ($reg->nim ?? $reg->id) . ($ext ? ".{$ext}" : '');

    return response()->download($fullPath, $filename);
}


}
