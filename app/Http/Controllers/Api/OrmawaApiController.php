<?php

namespace App\Http\Controllers\Api;

use App\Models\Ormawa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class OrmawaApiController extends BaseApiController
{
    /**
     * GET /api/ormawa
     * Public list untuk FE (dropdown, list, dsb)
     * Query optional: ?q=keyword
     */
    public function index(Request $request)
    {
        $q = $request->query('q');

        $ormawa = Ormawa::query()
            ->with('user')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->latest()
            ->get();

        return $this->success($ormawa, 'Daftar ormawa');
    }

    /**
     * GET /api/ormawa/{id}
     * Public detail
     */
    public function show($id)
    {
        $ormawa = Ormawa::with(['user'])->findOrFail($id);
        return $this->success($ormawa, 'Detail ormawa');
    }

    /**
     * POST /api/admin/ormawa
     * Admin create
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'type_ormawa'     => 'nullable|string|max:100',
            'category_ormawa' => 'nullable|string|max:100',
            'status_oprec'    => 'nullable|in:BUKA,TUTUP,SEGERA_DITUTUP',
            'description'     => 'nullable|string',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('ormawa', 'public');
        }

        $ormawa = Ormawa::create([
            'user_id'         => $request->user()->id,
            'name'            => $request->name,
            'photo_path'      => $photoPath,
            'type_ormawa'     => $request->type_ormawa,
            'category_ormawa' => $request->category_ormawa,
            'status_oprec'    => $request->status_oprec,
            'description'     => $request->description,
        ]);

        return $this->success($ormawa, 'Ormawa berhasil ditambahkan', 201);
    }


    /**
     * PUT /api/admin/ormawa/{id}
     * Admin update
     */
    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'type_ormawa'     => 'nullable|string|max:100',
            'category_ormawa' => 'nullable|string|max:100',
            'status_oprec'    => 'nullable|in:BUKA,TUTUP,SEGERA_DITUTUP',
            'description'     => 'nullable|string',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $ormawa = Ormawa::findOrFail($id);

        if ($request->hasFile('photo')) {
            // hapus foto lama
            if ($ormawa->photo_path) {
                Storage::disk('public')->delete($ormawa->photo_path);
            }

            $ormawa->photo_path = $request->file('photo')->store('ormawa', 'public');
        }

        $ormawa->update([
            'name'            => $request->name,
            'type_ormawa'     => $request->type_ormawa,
            'category_ormawa' => $request->category_ormawa,
            'status_oprec'    => $request->status_oprec,
            'description'     => $request->description,
        ]);

        return $this->success($ormawa, 'Ormawa berhasil diperbarui');
    }


    /**
     * DELETE /api/admin/ormawa/{id}
     * Admin delete (soft delete)
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $ormawa = Ormawa::findOrFail($id);
        $ormawa->delete();

        return $this->success(null, 'Ormawa berhasil dihapus');
    }
    public function adminIndex(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return $this->error('Akses hanya untuk admin.', 403);
        }

        $ormawas = Ormawa::latest('created_at')->get();
        return $this->success($ormawas, 'Daftar ormawa (admin)');
    }

}
