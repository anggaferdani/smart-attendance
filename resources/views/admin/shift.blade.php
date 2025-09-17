@extends('templates.admin')
@section('title', 'Shift')
@section('header')
<div class="container-xl">
  <div class="row g-2 align-items-center">
    <div class="col">
      <h2 class="page-title">Shift</h2>
    </div>
    <div class="col-auto">
      <div class="btn-list">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Shift</a>
        <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">Import Excel</a>
      </div>
    </div>
  </div>
</div>
@endsection
@section('content')
<div class="container-xl">
  <div class="row">
    <div class="col-12">
      @if(Session::get('success'))
        <div class="alert alert-important alert-success" role="alert">{{ Session::get('success') }}</div>
      @endif
      @if(Session::get('error'))
        <div class="alert alert-important alert-danger" role="alert">{{ Session::get('error') }}</div>
      @endif
      <div class="card">
        <div class="card-header">
          <div class="ms-auto">
            <form action="{{ route('admin.shift.index') }}" method="GET">
              <div class="d-flex gap-1">
                <input type="date" class="form-control" name="tanggal" value="{{ request('tanggal') }}">
                <button type="submit" class="btn btn-icon btn-dark-outline"><i class="fa-solid fa-magnifying-glass"></i></button>
                <a href="{{ route('admin.shift.index') }}" class="btn btn-icon btn-dark-outline"><i class="fa-solid fa-times"></i></a>
              </div>
            </form>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-striped">
            <thead>
              <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>User</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($shifts as $key => $shift)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($shift->first()->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $shift->first()->shift }}</td>
                <td>
                  @foreach($shift as $s)
                    <span class="badge bg-blue text-white">{{ $s->user->name }}</span>
                  @endforeach
                </td>
                <td>
                  <button type="button" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#edit{{ $key }}"><i class="fa-solid fa-pen"></i></button>
                  <button type="button" class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $key }}"><i class="fa-solid fa-trash"></i></button>
                </td>
              </tr>
              @empty
              <tr><td colspan="5" class="text-center">Tidak ada data shift</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="{{ route('admin.shift.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Create</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label required">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" value="{{ old('tanggal') }}">
          </div>
          <div class="mb-3">
            <label class="form-label required">Shift</label>
            <select class="form-select" name="shift">
              <option disabled selected value="">Pilih</option>
              <option value="WFO">WFO</option>
              <option value="WFH">WFH</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label required">User</label>
            <select class="form-select select2" name="user_ids[]" multiple>
              @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</a>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach ($shifts as $key => $shift)
<div class="modal modal-blur fade" id="edit{{ $key }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="{{ route('admin.shift.update', ['tanggal' => $shift->first()->tanggal, 'shift' => $shift->first()->shift]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label required">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" value="{{ $shift->first()->tanggal }}">
          </div>
          <div class="mb-3">
            <label class="form-label required">Shift</label>
            <select class="form-select" name="shift">
              <option value="WFO" @if($shift->first()->shift == 'WFO') selected @endif>WFO</option>
              <option value="WFH" @if($shift->first()->shift == 'WFH') selected @endif>WFH</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label required">User</label>
            <select class="form-select select2" name="user_ids[]" multiple>
              @foreach($users as $user)
                <option value="{{ $user->id }}" 
                  @if($shift->pluck('user_id')->contains($user->id)) selected @endif>
                  {{ $user->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</a>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

@foreach ($shifts as $key => $shift)
<div class="modal modal-blur fade" id="delete{{ $key }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      <div class="modal-status bg-danger"></div>
      <form action="{{ route('admin.shift.destroy', ['tanggal' => $shift->first()->tanggal, 'shift' => $shift->first()->shift]) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body text-center py-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.7 2.983h16.845a1.989 1.989 0 0 0 1.7 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.4 0z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
          <h3>Are you sure?</h3>
          <div class="text-secondary">Data shift ini akan dihapus dan tidak bisa dikembalikan.</div>
        </div>
        <div class="modal-footer">
          <div class="w-100">
            <div class="row">
              <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Cancel</a></div>
              <div class="col"><button type="submit" class="btn btn-danger w-100">Delete</button></div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

<div class="modal modal-blur fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <form action="{{ route('admin.shift.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Import Excel</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label required">File Excel</label>
            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            <small class="text-muted">
              Upload file Excel sesuai format. Klik di sini untuk 
              <a href="{{ route('admin.shift.template') }}" class="text-primary text-decoration-underline">Download Template Shift</a>.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</a>
          <button type="submit" class="btn btn-success">Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('.select2').select2({
      dropdownParent: $('.modal.show'),
      width: '100%'
    });
    $(document).on('shown.bs.modal', '.modal', function () {
      $(this).find('.select2').select2({
        dropdownParent: $(this),
        width: '100%'
      });
    });
  });
</script>
@endpush
