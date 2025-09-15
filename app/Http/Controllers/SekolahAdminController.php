<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;

class SekolahAdminController extends Controller
{
    public function index(Request $request) {
        $query = Sekolah::where('status', 1);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        }

        $sekolahs = $query->latest()->paginate(10);

        return view('admin.sekolah.sekolah', compact(
            'sekolahs',
        ));
    }

    public function create() {}

    public function store(Request $request) {
        $request->validate([
            'nama' => 'required',
        ]);

        try {
            $array = [
                'nama' => $request['nama'],
                'keterangan' => $request['keterangan'],
            ];

            Sekolah::create($array);
    
            return redirect()->back()->with('success', 'Success.');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function show($id) {}

    public function edit($id) {}

    public function update(Request $request, $id) {
        $request->validate([
            'nama' => 'required',
        ]);

        try {
            $sekolah = Sekolah::find($id);
    
            $array = [
                'nama' => $request['nama'],
                'keterangan' => $request['keterangan'],
            ];

            $sekolah->update($array);
    
            return redirect()->back()->with('success', 'Success.');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroy($id) {
        try {
            $sekolah = Sekolah::find($id);

            $sekolah->update([
                'status' => 2,
            ]);

            return redirect()->back()->with('success', 'Success.');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }
}
