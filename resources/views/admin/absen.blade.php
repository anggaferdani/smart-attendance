@extends('templates.admin')
@section('title', 'Absen')
@section('header')
<div class="container-xl">
  <div class="row g-2 align-items-center">
    <div class="col">
      <h2 class="page-title">
        Absen
      </h2>
    </div>
    <div class="col-auto ms-auto">
      <div class="btn-list">
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalAbsenFull">
          Lihat Absen Bulanan
      </button>
        <a href="{{ route('admin.absen.export', ['format' => 'excel', 'bulan' => request('bulan'), 'tahun' => request('tahun')]) }}" 
          class="btn btn-success" target="_blank">
          Export Excel
        </a>
        <a href="{{ route('admin.absen.export', ['format' => 'pdf', 'bulan' => request('bulan'), 'tahun' => request('tahun')]) }}" 
          class="btn btn-danger" target="_blank">
          Export PDF
        </a>
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
        <div class="alert alert-important alert-success" role="alert">
          {{ Session::get('success') }}
        </div>
      @endif
      @if(Session::get('error'))
        <div class="alert alert-important alert-danger" role="alert">
          {{ Session::get('error') }}
        </div>
      @endif
      <div class="card">
        <div class="card-header">
          <div class="ms-auto">
            <form action="{{ route('admin.absen') }}" class="">
              <div class="d-flex gap-1">
                <select class="form-select" name="status_absen">
                  <option disabled selected value="">Status</option>
                  <option value="">Semua</option>
                  <option value="1" {{ request('status_absen') == '1' ? 'selected' : '' }}>Masuk</option>
                  <option value="2" {{ request('status_absen') == '2' ? 'selected' : '' }}>Pulang</option>
                </select>
                <select class="form-select" name="lokasi">
                    <option disabled selected value="">Lokasi</option>
                    <option value="">Semua</option>
                    @foreach($lokasis as $lokasi)
                        <option value="{{ $lokasi->id }}" {{ request('lokasi') == $lokasi->id ? 'selected' : '' }}>
                            {{ $lokasi->nama }}
                        </option>
                    @endforeach
                </select>
                <select class="form-select" name="status">
                    <option disabled selected value="">Status Kedatangan</option>
                    <option value="">Semua</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Lebih Awal</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Tepat Waktu</option>
                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Terlambat</option>
                </select>
                <input type="text" id="dateRangePicker" class="form-control" name="date_range" value="{{ request('date_range') }}" placeholder="Dari - Sampai" autocomplete="off">
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search">
                <button type="submit" class="btn btn-icon btn-dark-outline"><i class="fa-solid fa-magnifying-glass"></i></button>
                <a href="{{ route('admin.absen') }}" class="btn btn-icon btn-dark-outline"><i class="fa-solid fa-times"></i></a>
              </div>
            </form>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-striped">
            <thead>
              <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Kode</th>
                <th>Token</th>
                <th>Shift</th>
                <th>Status Absen</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($absens as $absen)
                <tr>
                  <td>{{ ($absens->currentPage() - 1) * $absens->perPage() + $loop->iteration }}</td>
                  <td>{{ $absen->user->name }}</td>
                  <td>{{ $absen->tanggal }}</td>
                  <td>{{ $absen->kode }}</td>
                  <td>{{ $absen->token->token }}</td>
                  <td>
                    @if($absen->shift == 'WFH')
                      <span class="badge bg-yellow text-yellow-fg">WFH</span>
                    @elseif($absen->shift == 'WFO')
                      <span class="badge bg-dark text-dark-fg">WFO</span>
                    @endif
                  </td>
                  <td>
                    @if($absen->token->status == 1)
                      <span class="badge bg-blue text-blue-fg">Masuk</span>
                    @elseif($absen->token->status == 2)
                      <span class="badge bg-red text-red-fg">Pulang</span>
                    @endif
                  </td>
                  <td>
                    @if($absen->token->status == 1)
                      @if($absen->status == 1)
                        <span class="badge bg-blue text-blue-fg">Lebih Awal</span>
                      @elseif($absen->status == 2)
                        <span class="badge bg-green text-green-fg">Tepat Waktu</span>
                      @elseif($absen->status == 3)
                        <span class="badge bg-red text-red-fg">Terlambat</span>
                      @endif
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    <button type="button" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#show{{ $absen->id }}"><i class="fa-solid fa-eye"></i></button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="card-footer d-flex align-items-center">
          <ul class="pagination m-0 ms-auto">
            @if($absens->hasPages())
              {{ $absens->appends(request()->query())->links('pagination::bootstrap-4') }}
            @else
              <li class="page-item">No more records</li>
            @endif
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

@foreach ($absens as $absen)
<div class="modal modal-blur fade" id="show{{ $absen->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="" method="POST" class="">
        <div class="modal-header">
          <h5 class="modal-title">Kode {{ $absen->kode }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div>Token : {{ $absen->kode }}</div>
          <div>Token : {{ $absen->token->token }}</div>
          <div>Lokasi : {{ $absen->token->lokasi->nama }}</div>
          <div>Shift : @if($absen->shift == 'WFH') <span class="badge bg-yellow text-yellow-fg">WFH</span> @elseif($absen->shift == 'WFO') <span class="badge bg-dark text-dark-fg">WFO</span> @endif</div>
          <div>Status : @if($absen->token->status == 1) <span class="badge bg-blue text-blue-fg">Masuk</span> @elseif($absen->token->status == 2) <span class="badge bg-red text-red-fg">Pulang</span> @endif</div>
          <div>Nama : {{ $absen->user->name }}</div>
          <div>Email : {{ $absen->user->email }}</div>
          <div>Tanggal : {{ $absen->tanggal }}</div>
          <div>Status : @if($absen->token->status == 1) Masuk @if($absen->status == 1) Lebih Awal @elseif($absen->status == 2) Tepat Waktu @elseif($absen->status == 3) Terlambat @endif @elseif($absen->token->status == 2) Pulang @endif</div>
          <div>Lat : {{ $absen->lat }}</div>
          <div>Long : {{ $absen->long }}</div>
        </div>
        <div class="modal-footer">
          <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

<div class="modal fade" id="modalAbsenFull" tabindex="-1" aria-labelledby="modalAbsenFullLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAbsenFullLabel">Rekap Absen Bulanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @php
          $currentYear = now()->year;
          $currentMonth = now()->month;
        @endphp
        <div class="row g-2 mb-3">
          <div class="col-auto">
            <select id="selectBulan" class="form-select">
              @for($m=1; $m<=12; $m++)
                <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                  {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
              @endfor
            </select>
          </div>
          <div class="col-auto">
            <select id="selectTahun" class="form-select">
              @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
              @endfor
            </select>
          </div>
          <div class="col-auto">
            <button id="btnLoadRekap" class="btn btn-primary">Tampilkan</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-sm" style="font-size: 10px;">
            <thead>
              <tr>
                <th>Nama</th>
                @for($d = 1; $d <= 31; $d++)
                  <th>{{ $d }}</th>
                @endfor
                <th>Hadir</th>
                <th>Terlambat</th>
                <th>Izin</th>
                <th>Sakit</th>
              </tr>
            </thead>
            <tbody id="rekapAbsenBody">
              <tr><td colspan="35" class="text-center">Pilih bulan dan tahun...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  $(function() {
      $('#dateRangePicker').daterangepicker({
          locale: {
              format: 'YYYY-MM-DD'
          },
          autoUpdateInput: false,
      });
  
      $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
          $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
      });
  
      $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
          $(this).val('');
      });
  });
</script>
<script>
  function loadRekap(bulan, tahun) {
      $.ajax({
          url: "{{ route('admin.absen') }}",
          type: 'GET',
          data: { ajax_rekap: true, bulan: bulan, tahun: tahun },
          success: function(res) {
              $('#rekapAbsenBody').html(res);
          },
          error: function() {
              alert('Gagal memuat rekap!');
          }
      });
  }

  $('#btnLoadRekap').on('click', function() {
      let bulan = $('#selectBulan').val();
      let tahun = $('#selectTahun').val();
      loadRekap(bulan, tahun);
  });

  $('#modalAbsenFull').on('shown.bs.modal', function() {
      let bulan = $('#selectBulan').val();
      let tahun = $('#selectTahun').val();
      loadRekap(bulan, tahun);
  });
</script>
@endpush