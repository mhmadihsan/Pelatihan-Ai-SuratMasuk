# 09 Rute
Status: FINAL

Satu-satunya rumah daftar rute. Aturan umum: semua rute selain `/login` butuh login; saat `wajib_ganti_password` aktif hanya `/ganti-password` dan `/logout` yang boleh diakses (BR-08 di `docs/06_BUSINESS_RULES.md`). Semua POST wajib token CSRF (`docs/13_SECURITY.md`). `{id}` = bilangan bulat.

| Metode | Path | Handler | Akses | FR |
|---|---|---|---|---|
| GET | `/login` | AuthController@form | tamu | FR-01 |
| POST | `/login` | AuthController@login | tamu | FR-01 |
| POST | `/logout` | AuthController@logout | login | FR-01 |
| GET | `/ganti-password` | AuthController@formGanti | login | FR-02 |
| POST | `/ganti-password` | AuthController@ganti | login | FR-02 |
| GET | `/` | DashboardController@index | login | FR-12 |
| GET | `/surat` | SuratController@index | login | FR-04 |
| GET | `/surat/tambah` | SuratController@create | login | FR-03 |
| POST | `/surat` | SuratController@store | login | FR-03 |
| GET | `/surat/{id}` | SuratController@show | login | FR-05 |
| GET | `/surat/{id}/ubah` | SuratController@edit | login | FR-06 |
| POST | `/surat/{id}` | SuratController@update | login | FR-06 |
| POST | `/surat/{id}/hapus` | SuratController@destroy | admin | FR-07 |
| GET | `/laporan` | LaporanController@index | login | FR-08 |
| GET | `/laporan/cetak` | LaporanController@cetak | login | FR-09 |
| GET | `/laporan/ekspor` | LaporanController@ekspor | login | FR-10 |
| GET | `/pengguna` | PenggunaController@index | admin | FR-11 |
| GET | `/pengguna/tambah` | PenggunaController@create | admin | FR-11 |
| POST | `/pengguna` | PenggunaController@store | admin | FR-11 |
| GET | `/pengguna/{id}/ubah` | PenggunaController@edit | admin | FR-11 |
| POST | `/pengguna/{id}` | PenggunaController@update | admin | FR-11 |
| POST | `/pengguna/{id}/reset-password` | PenggunaController@resetPassword | admin | FR-11 |
| POST | `/pengguna/{id}/status` | PenggunaController@toggleStatus | admin | FR-11 |

Parameter query `/surat`: `q` (cari di nomor surat, asal surat, perihal), `sifat`, `dari`, `sampai`, `page`. Parameter laporan ada di `docs/14_REPORTS_EXPORT.md`.
