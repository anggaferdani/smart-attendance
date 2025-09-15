<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shift;
use App\Imports\ShiftImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ShiftTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class ShiftController extends Controller
{
    public function template()
    {
        return Excel::download(new ShiftTemplateExport, 'template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new ShiftImport, $request->file('file'));

        return redirect()->route('admin.shift.index')->with('success', 'Data shift berhasil diimport.');
    }

    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal');

        $users = User::where('role', 2)
            ->where('status', 1)
            ->get();

        $shifts = Shift::with('user')
            ->when($tanggal, fn($q) => $q->whereDate('tanggal', $tanggal))
            ->orderBy('tanggal', 'desc')
            ->get()
            ->groupBy(fn($item) => $item->tanggal . '-' . $item->shift);


        return view('admin.shift', compact('users', 'shifts', 'tanggal'));
    }

    public function create() {}

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->user_ids as $userId) {
                Shift::create([
                    'tanggal' => $request->tanggal,
                    'shift' => $request->shift,
                    'user_id' => $userId,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Jadwal shift berhasil disimpan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function show($id) {}

    public function edit($id) {}

    public function update(Request $request, $tanggal, $shift)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'shift' => 'required',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            Shift::where('tanggal', $tanggal)
                ->where('shift', $shift)
                ->delete();

            foreach ($request->user_ids as $userId) {
                Shift::create([
                    'tanggal' => $request->tanggal,
                    'shift' => $request->shift,
                    'user_id' => $userId,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Shift berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroy($tanggal, $shift)
    {
        try {
            Shift::where('tanggal', $tanggal)
                ->where('shift', $shift)
                ->delete();

            return redirect()->back()->with('success', 'Shift berhasil dihapus.');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }
}
