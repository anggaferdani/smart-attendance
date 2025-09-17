<div style="width: 100%; max-width: 900px; margin: 0 auto;">
  <div>
    <div style="padding: 20px;">
      <div style="position: relative; margin-bottom: 30px; text-align: center;">
        <div>
          <div style="font-weight: bold; font-size: 1rem;">{{ $izin->user->lokasi->nama }}</div>
          <div style="font-weight: bold;">{{ $izin->user->lokasi->deskripsi }}</div>
        </div>
      </div>
      <hr style="margin-bottom: 30px;">
      <div style="text-align: center; font-size: 1rem; font-weight: bold; margin-bottom: 30px;">FORMULIR PENGAJUAN IZIN</div>
      <div style="margin-bottom: 15px;">Kepada YTH</div>
      <div>Penanggung Jawab</div>
      <div>di</div>
      <div style="margin-bottom: 15px;">Tempat</div>
      <div style="margin-bottom: 15px;">Perihal : {{ Str::title(str_replace('-', ' ', $izin->alasan)) }}</div>
      <div style="margin-bottom: 15px;">Saya yang bertanda tangan dibawah ini:</div>
      <table style="width: 100%; margin-bottom: 15px;">
        <tr>
          <td style="width: 20%;">Nama</td>
          <td>: <strong>{{ $izin->user->name }}</strong></td>
        </tr>
        <tr>
          <td style="width: 20%;">Jabatan</td>
          <td>: <strong>{{ $izin->user->jabatan }}</strong></td>
        </tr>
        <tr>
          <td style="width: 20%;">Sekolah</td>
          <td>: {{ $izin->user->sekolah->nama ?? '-' }}</td>
        </tr>
        <tr>
          <td style="width: 20%;">Lokasi</td>
          <td>: {{ $izin->user->lokasi->nama }}, {{ $izin->user->lokasi->deskripsi }}</td>
        </tr>
      </table>
      <div style="margin-bottom: 15px;">
        Bermaksud untuk mengajukan permohonan izin.
      </div>
      <div style="margin-bottom: 15px;">
        Demikian surat permohonan ini saya buat. Atas perhatian nya saya ucapkan terima kasih.
      </div>
      <div style="margin-bottom: 15px;">
        <strong>{{ \Carbon\Carbon::parse($izin->sampai)->locale('id')->translatedFormat('l, d F Y') }}</strong>
      </div>
      <div style="margin-bottom: 30px;">Hormat Saya,</div>
      <div style="margin-bottom: 30px;"><strong>{{ $izin->user->name }}</strong></div>

      @if($izin->lampiran)
      <table style="border-collapse: collapse;">
        <tr>
          <td>Lampiran :</td>
        </tr>
        <tr>
          <td valign="top">
            <img src="{{ public_path('izin/' . $izin->lampiran) }}" width="130">
          </td>
        </tr>
      </table>
      @endif
    </div>
  </div>
</div>
