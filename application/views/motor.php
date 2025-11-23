<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Catatan Pengeluaran Motor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

    <style>
        body {
            background-color: #f5f5f5;
            padding-bottom: 70px;
        }

        .app-header {
            background: linear-gradient(135deg, #0d6efd, #39a6ff);
            color: #fff;
            padding: 1rem 1.25rem 1.25rem;
            border-bottom-left-radius: 1.25rem;
            border-bottom-right-radius: 1.25rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
        }

        .app-header h1 {
            font-size: 1.15rem;
            margin: 0;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #fff;
            border-top: 1px solid #dee2e6;
        }

        .bottom-nav .nav-link {
            font-size: .8rem;
            padding: .35rem 0;
            color: #6c757d;
        }

        .bottom-nav .nav-link i {
            font-size: 1.2rem;
            display: block;
        }

        .bottom-nav .nav-link.active {
            color: #0d6efd;
            font-weight: 600;
        }

        .page {
            display: none;
        }

        .page.active {
            display: block;
        }

        .section-title {
            font-size: .95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            margin-bottom: .5rem;
        }

        .filter-chip {
            font-size: .75rem;
        }
    </style>
</head>

<body>

    <header class="app-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Catatan Motor</h1>
                <small class="text-light-50">Pantau pengeluaran bensin, oli, dan service</small>
            </div>
            <div class="text-end">
                <div class="fw-semibold small"><?= html_escape($motor->nama ?? 'Motor Saya'); ?></div>
                <div class="small text-light-50">Plat: <?= html_escape($motor->plat_nomor ?? '-'); ?></div>
            </div>
        </div>
    </header>

    <main class="container py-3">
        <?php if ($this->session->flashdata('msg')): ?>
            <div class="alert alert-success py-2 small mb-2"><?= $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <!-- DASHBOARD -->
        <section id="page-dashboard" class="page active">
            <!-- Filter Bulan / Tahun -->
            <div class="mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="section-title mb-0">Analitik Pengeluaran</div>
                    <span class="badge bg-light text-secondary filter-chip">
                        <i class="bi bi-bar-chart-line me-1"></i> Per Bulan
                    </span>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-5">
                                <label class="form-label small mb-1">Tahun</label>
                                <select id="filterYear" class="form-select form-select-sm">
                                    <?php $yearNow = date('Y'); ?>
                                    <option value="<?= $yearNow; ?>" selected><?= $yearNow; ?></option>
                                </select>
                            </div>
                            <div class="col-5">
                                <label class="form-label small mb-1">Bulan</label>
                                <select id="filterMonth" class="form-select form-select-sm">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11" selected>November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                            <div class="col-2 d-flex align-items-end">
                                <button id="btnResetFilter" type="button" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="small text-muted mt-2" id="labelPeriode">Periode: -</div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Analitik -->
            <div class="mb-3" id="dash-analytics">
                <div class="row g-2 mb-2">
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-2">
                                <div class="small text-muted">Total Pengeluaran</div>
                                <div class="fw-bold" id="valTotal">Rp 0</div>
                                <div class="badge bg-light text-secondary mt-1" id="valTransaksi">0 Transaksi</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-2">
                                <div class="small text-muted">Minyak</div>
                                <div class="fw-bold" id="valMinyak">Rp 0</div>
                                <div class="badge bg-light text-secondary mt-1" id="valMinyakCount">0x Isi</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-2">
                                <div class="small text-muted">Service + Oli</div>
                                <div class="fw-bold" id="valServiceOli">Rp 0</div>
                                <div class="badge bg-light text-secondary mt-1" id="valServiceOliCount">0x</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel ringkasan -->
                <div class="card border-0 shadow-sm mb-2">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold text-uppercase text-muted">Ringkasan Kategori</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr class="small">
                                        <th>Kategori</th>
                                        <th class="text-end">Total (Rp)</th>
                                        <th class="text-end">Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <tr>
                                        <td>Minyak</td>
                                        <td class="text-end" id="tblMinyakTotal">Rp 0</td>
                                        <td class="text-end" id="tblMinyakCount">0x</td>
                                    </tr>
                                    <tr>
                                        <td>Service</td>
                                        <td class="text-end" id="tblServiceTotal">Rp 0</td>
                                        <td class="text-end" id="tblServiceCount">0x</td>
                                    </tr>
                                    <tr>
                                        <td>Ganti Oli</td>
                                        <td class="text-end" id="tblOliTotal">Rp 0</td>
                                        <td class="text-end" id="tblOliCount">0x</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light small">
                                    <tr>
                                        <th>Jarak Tempuh</th>
                                        <th class="text-end" colspan="2" id="tblKmRange">-</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Progress km -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">Perkiraan penggunaan bulan ini</span>
                            <span id="kmUsedLabel">0 km</span>
                        </div>
                        <div class="progress" style="height:6px;">
                            <div id="kmProgress" class="progress-bar" role="progressbar" style="width:0%;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1 text-muted">
                            <span class="small" id="kmStartLabel">Awal: -</span>
                            <span class="small" id="kmEndLabel">Akhir: -</span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="dash-empty" class="alert alert-light border small d-none">
                Belum ada data pengeluaran untuk bulan/tahun yang dipilih.
            </div>

            <!-- (opsional) bisa tambahkan ringkasan catatan terakhir di dashboard -->
        </section>

        <!-- MINYAK -->
        <section id="page-minyak" class="page">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="section-title mb-0">Minyak</div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMinyak">
                        <i class="bi bi-plus-circle me-1"></i> Tambah
                    </button>
                </div>

                <div class="section-title" style="font-size:0.8rem;">Riwayat Pengisian</div>
                <div class="list-group small shadow-sm">
                    <?php if (!empty($minyak_list)): ?>
                        <?php foreach ($minyak_list as $row): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-semibold">
                                            <?= date('d M Y', strtotime($row->tanggal)); ?>
                                            <span class="badge bg-primary-subtle text-primary ms-1">Minyak</span>
                                        </div>
                                        <div class="text-muted">
                                            KM <?= number_format($row->odo_display_km, 0, ',', '.'); ?>
                                            <?php if (!empty($row->jenis_bbm)): ?> • <?= html_escape($row->jenis_bbm); ?><?php endif; ?>
                                                <?php if (!empty($row->isi_liter)): ?> • <?= rtrim(rtrim(number_format($row->isi_liter, 2, ',', '.'), '0'), ','); ?> L<?php endif; ?>
                                                    <?php if (!empty($row->sisa_minyak_batang)): ?> • Sisa <?= (int)$row->sisa_minyak_batang; ?> batang<?php endif; ?>
                                        </div>

                                        <!-- 👇 TAMBAHAN: jarak & selisih hari -->
                                        <?php if ($row->jarak_km !== null): ?>
                                            <div class="text-muted small">
                                                Jarak tempuh: <strong><?= number_format($row->jarak_km, 0, ',', '.'); ?> km</strong>
                                                <?php if ($row->selisih_hari === 0): ?>
                                                    • di hari yang sama dengan isi sebelumnya
                                                <?php elseif ($row->selisih_hari === 1): ?>
                                                    • 1 hari sejak isi sebelumnya
                                                <?php elseif ($row->selisih_hari > 1): ?>
                                                    • <?= (int)$row->selisih_hari; ?> hari sejak isi sebelumnya
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small fst-italic">
                                                Isi pertama (tidak ada data sebelumnya)
                                            </div>
                                        <?php endif; ?>
                                        <!-- 👆 END TAMBAHAN -->

                                        <?php if (!empty($row->lokasi_label)): ?>
                                            <div class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i><?= html_escape($row->lokasi_label); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($row->catatan)): ?>
                                            <div class="text-muted fst-italic">
                                                "<?= html_escape($row->catatan); ?>"
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary">
                                            <?= $row->total_uang ? 'Rp ' . number_format($row->total_uang, 0, ',', '.') : '-'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="list-group-item text-muted fst-italic">
                            Belum ada riwayat pengisian minyak.
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <!-- SERVICE -->
        <section id="page-service" class="page">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="section-title mb-0">Service</div>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalService">
                        <i class="bi bi-wrench-adjustable me-1"></i> Tambah
                    </button>
                </div>

                <div class="section-title" style="font-size:0.8rem;">Riwayat Service</div>
                <div class="list-group small shadow-sm">
                    <?php if (!empty($service_list)): ?>
                        <?php foreach ($service_list as $row): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-semibold">
                                            <?= date('d M Y', strtotime($row->tanggal)); ?>
                                            <span class="badge bg-success-subtle text-success ms-1">Service</span>
                                        </div>
                                        <div class="text-muted">
                                            KM <?= number_format($row->odo_display_km, 0, ',', '.'); ?>
                                        </div>
                                        <?php if (!empty($row->lokasi_label)): ?>
                                            <div class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i><?= html_escape($row->lokasi_label); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($row->keterangan)): ?>
                                            <div class="text-muted">
                                                <?= nl2br(html_escape($row->keterangan)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success">
                                            <?= $row->harga ? 'Rp ' . number_format($row->harga, 0, ',', '.') : '-'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="list-group-item text-muted fst-italic">
                            Belum ada riwayat service.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- GANTI OLI -->
        <section id="page-oli" class="page">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="section-title mb-0">Ganti Oli</div>
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalOli">
                        <i class="bi bi-droplet-half me-1"></i> Tambah
                    </button>
                </div>

                <div class="section-title" style="font-size:0.8rem;">Riwayat Ganti Oli</div>
                <div class="list-group small shadow-sm">
                    <?php if (!empty($oli_list)): ?>
                        <?php foreach ($oli_list as $row): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-semibold">
                                            <?= date('d M Y', strtotime($row->tanggal)); ?>
                                            <span class="badge bg-warning-subtle text-warning ms-1">Ganti Oli</span>
                                        </div>
                                        <div class="text-muted">
                                            KM <?= number_format($row->odo_display_km, 0, ',', '.'); ?>
                                            <?php if (!empty($row->merek_oli)): ?> • <?= html_escape($row->merek_oli); ?><?php endif; ?>
                                        </div>
                                        <?php if (!empty($row->lokasi_label)): ?>
                                            <div class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i><?= html_escape($row->lokasi_label); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($row->keterangan)): ?>
                                            <div class="text-muted">
                                                <?= nl2br(html_escape($row->keterangan)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-warning">
                                            <?= $row->harga ? 'Rp ' . number_format($row->harga, 0, ',', '.') : '-'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="list-group-item text-muted fst-italic">
                            Belum ada riwayat ganti oli.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </main>

    <!-- BOTTOM NAV -->
    <nav class="bottom-nav">
        <div class="container">
            <ul class="nav justify-content-around text-center small">
                <li class="nav-item">
                    <button class="nav-link active" data-target="page-dashboard">
                        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-target="page-minyak">
                        <i class="bi bi-fuel-pump"></i><span>Minyak</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-target="page-service">
                        <i class="bi bi-tools"></i><span>Service</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-target="page-oli">
                        <i class="bi bi-droplet"></i><span>Ganti Oli</span>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <!-- MODAL: MINYAK -->
    <div class="modal fade" id="modalMinyak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title small">
                        <i class="bi bi-fuel-pump me-1"></i> Catat Pengisian Minyak
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="<?= base_url('motor/simpan_minyak'); ?>">
                        <input type="hidden" name="motor_id" value="<?= (int)$motor->id; ?>">

                        <div class="mb-2">
                            <label class="form-label small mb-1">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm" required />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Kilometer Terakhir (display)</label>
                            <input type="number" name="odo_display_km" class="form-control form-control-sm" placeholder="contoh: 24500" required />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Sisa Minyak (batang)</label>
                            <select name="sisa_minyak_batang" class="form-select form-select-sm">
                                <option value="">Pilih</option>
                                <option value="1">1 batang</option>
                                <option value="2">2 batang</option>
                                <option value="3">3 batang</option>
                                <option value="4">4 batang (penuh)</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Jenis BBM</label>
                            <select name="jenis_bbm" class="form-select form-select-sm">
                                <option value="Pertalite">Pertalite</option>
                                <option value="Pertamax">Pertamax</option>
                                <option value="Pertamax Turbo">Pertamax Turbo</option>
                                <option value="Solar">Solar</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Isi Minyak (liter)</label>
                                <input type="number" step="0.1" name="isi_liter" class="form-control form-control-sm" placeholder="contoh: 1.5" />
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">Uang (Rp)</label>
                                <input type="number" name="total_uang" class="form-control form-control-sm" placeholder="contoh: 35000" />
                            </div>
                        </div>

                        <div class="mb-2 mt-2">
                            <label class="form-label small mb-1">Lokasi</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="minyakLocationLabel"
                                    name="lokasi_label" placeholder="Belum diambil, tekan ikon lokasi..." readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="getLocation('minyak')">
                                    <i class="bi bi-geo-alt"></i>
                                </button>
                            </div>
                            <input type="hidden" name="latitude" id="minyakLat">
                            <input type="hidden" name="longitude" id="minyakLng">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1">Catatan</label>
                            <textarea name="catatan" class="form-control form-control-sm" rows="2"></textarea>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check2-circle me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: SERVICE -->
    <div class="modal fade" id="modalService" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title small">
                        <i class="bi bi-tools me-1"></i> Catat Service
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="<?= base_url('motor/simpan_service'); ?>">
                        <input type="hidden" name="motor_id" value="<?= (int)$motor->id; ?>">

                        <div class="mb-2">
                            <label class="form-label small mb-1">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm" required />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Kilometer Terakhir (display)</label>
                            <input type="number" name="odo_display_km" class="form-control form-control-sm" placeholder="contoh: 23700" required />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Harga (Rp)</label>
                            <input type="number" name="harga" class="form-control form-control-sm" placeholder="contoh: 50000" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Keterangan</label>
                            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="contoh: Bersih karburator, cek kampas rem, setel rantai"></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1">Lokasi</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="serviceLocationLabel"
                                    name="lokasi_label" placeholder="Belum diambil, tekan ikon lokasi..." readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="getLocation('service')">
                                    <i class="bi bi-geo-alt"></i>
                                </button>
                            </div>
                            <input type="hidden" name="latitude" id="serviceLat">
                            <input type="hidden" name="longitude" id="serviceLng">
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-check2-circle me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: GANTI OLI -->
    <div class="modal fade" id="modalOli" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title small">
                        <i class="bi bi-droplet-half me-1"></i> Catat Ganti Oli
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="<?= base_url('motor/simpan_oli'); ?>">
                        <input type="hidden" name="motor_id" value="<?= (int)$motor->id; ?>">

                        <div class="mb-2">
                            <label class="form-label small mb-1">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm" required />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Kilometer Terakhir (display)</label>
                            <input type="number" name="odo_display_km" class="form-control form-control-sm" placeholder="contoh: 24000" required />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Merek Oli</label>
                            <input type="text" name="merek_oli" class="form-control form-control-sm" placeholder="contoh: Federal Matic 10W-30" required />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Harga (Rp)</label>
                            <input type="number" name="harga" class="form-control form-control-sm" placeholder="contoh: 75000" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Keterangan</label>
                            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="contoh: Termasuk jasa ganti oli"></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1">Lokasi</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="oliLocationLabel"
                                    name="lokasi_label" placeholder="Belum diambil, tekan ikon lokasi..." readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="getLocation('oli')">
                                    <i class="bi bi-geo-alt"></i>
                                </button>
                            </div>
                            <input type="hidden" name="latitude" id="oliLat">
                            <input type="hidden" name="longitude" id="oliLng">
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="bi bi-check2-circle me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Switch page
        (function() {
            const navLinks = document.querySelectorAll(".bottom-nav .nav-link");
            const pages = document.querySelectorAll(".page");

            function showPage(id) {
                pages.forEach(p => p.classList.remove("active"));
                document.getElementById(id)?.classList.add("active");
                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }
            navLinks.forEach(btn => {
                btn.addEventListener("click", () => {
                    navLinks.forEach(b => b.classList.remove("active"));
                    btn.classList.add("active");
                    showPage(btn.getAttribute("data-target"));
                });
            });
        })();

        // Ambil lokasi GPS
        function getLocation(type) {
            if (!navigator.geolocation) {
                alert("Perangkat ini tidak mendukung GPS (geolocation).");
                return;
            }
            const map = {
                "minyak": {
                    label: document.getElementById("minyakLocationLabel"),
                    lat: document.getElementById("minyakLat"),
                    lng: document.getElementById("minyakLng"),
                },
                "service": {
                    label: document.getElementById("serviceLocationLabel"),
                    lat: document.getElementById("serviceLat"),
                    lng: document.getElementById("serviceLng"),
                },
                "oli": {
                    label: document.getElementById("oliLocationLabel"),
                    lat: document.getElementById("oliLat"),
                    lng: document.getElementById("oliLng"),
                }
            };
            const target = map[type];
            if (!target) return;
            target.label.value = "Mengambil lokasi...";

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    target.lat.value = lat;
                    target.lng.value = lng;
                    target.label.value = lat.toFixed(5) + ", " + lng.toFixed(5);
                },
                function(err) {
                    console.error(err);
                    alert("Gagal mengambil lokasi. Pastikan GPS aktif dan izin lokasi diizinkan.");
                    target.label.value = "";
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 600000
                }
            );
        }

        // Analytics dari backend
        (function() {
            const motorId = <?= (int)$motor->id; ?>;
            const yearSel = document.getElementById("filterYear");
            const monthSel = document.getElementById("filterMonth");
            const btnReset = document.getElementById("btnResetFilter");

            const labelPeriode = document.getElementById("labelPeriode");
            const dashAnalytics = document.getElementById("dash-analytics");
            const dashEmpty = document.getElementById("dash-empty");

            const valTotal = document.getElementById("valTotal");
            const valTransaksi = document.getElementById("valTransaksi");
            const valMinyak = document.getElementById("valMinyak");
            const valMinyakCount = document.getElementById("valMinyakCount");
            const valServiceOli = document.getElementById("valServiceOli");
            const valServiceOliCount = document.getElementById("valServiceOliCount");

            const tblMinyakTotal = document.getElementById("tblMinyakTotal");
            const tblMinyakCount = document.getElementById("tblMinyakCount");
            const tblServiceTotal = document.getElementById("tblServiceTotal");
            const tblServiceCount = document.getElementById("tblServiceCount");
            const tblOliTotal = document.getElementById("tblOliTotal");
            const tblOliCount = document.getElementById("tblOliCount");
            const tblKmRange = document.getElementById("tblKmRange");

            const kmProgress = document.getElementById("kmProgress");
            const kmUsedLabel = document.getElementById("kmUsedLabel");
            const kmStartLabel = document.getElementById("kmStartLabel");
            const kmEndLabel = document.getElementById("kmEndLabel");

            function formatRupiah(v) {
                return "Rp " + (v || 0).toLocaleString("id-ID");
            }

            function loadAnalytics() {
                const tahun = yearSel.value;
                const bulan = monthSel.value;
                const url = "<?= base_url('motor/get_analytics'); ?>?motor_id=" + motorId + "&tahun=" + tahun + "&bulan=" + bulan;

                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        if (data.error || (data.total === 0 && !data.km_start && !data.km_end)) {
                            dashAnalytics.classList.add("d-none");
                            dashEmpty.classList.remove("d-none");
                            labelPeriode.textContent = "Periode: -";
                            return;
                        }
                        dashAnalytics.classList.remove("d-none");
                        dashEmpty.classList.add("d-none");

                        labelPeriode.textContent = "Periode: " + (data.label || "-");

                        const totalTrans = (data.minyak?.count || 0) + (data.service?.count || 0) + (data.oli?.count || 0);
                        valTotal.textContent = formatRupiah(data.total || 0);
                        valTransaksi.textContent = totalTrans + " Transaksi";

                        valMinyak.textContent = formatRupiah(data.minyak?.total || 0);
                        valMinyakCount.textContent = (data.minyak?.count || 0) + "x Isi";

                        const serviceOliTotal = (data.service?.total || 0) + (data.oli?.total || 0);
                        const serviceOliCount = (data.service?.count || 0) + (data.oli?.count || 0);
                        valServiceOli.textContent = formatRupiah(serviceOliTotal);
                        valServiceOliCount.textContent = serviceOliCount + "x";

                        tblMinyakTotal.textContent = formatRupiah(data.minyak?.total || 0);
                        tblMinyakCount.textContent = (data.minyak?.count || 0) + "x";

                        tblServiceTotal.textContent = formatRupiah(data.service?.total || 0);
                        tblServiceCount.textContent = (data.service?.count || 0) + "x";

                        tblOliTotal.textContent = formatRupiah(data.oli?.total || 0);
                        tblOliCount.textContent = (data.oli?.count || 0) + "x";

                        if (data.km_start !== null && data.km_end !== null && data.km_end > data.km_start) {
                            const used = data.km_end - data.km_start;
                            kmUsedLabel.textContent = used + " km";
                            kmStartLabel.textContent = "Awal: " + data.km_start.toLocaleString("id-ID") + " km";
                            kmEndLabel.textContent = "Akhir: " + data.km_end.toLocaleString("id-ID") + " km";
                            const percent = Math.min((used / 1000) * 100, 100);
                            kmProgress.style.width = percent.toFixed(0) + "%";
                        } else {
                            kmUsedLabel.textContent = "0 km";
                            kmStartLabel.textContent = "Awal: -";
                            kmEndLabel.textContent = "Akhir: -";
                            kmProgress.style.width = "0%";
                        }

                        if (data.km_start !== null && data.km_end !== null) {
                            tblKmRange.textContent = data.km_start.toLocaleString("id-ID") + " → " + data.km_end.toLocaleString("id-ID") + " km";
                        } else {
                            tblKmRange.textContent = "-";
                        }
                    })
                    .catch(err => {
                        console.error(err);
                    });
            }

            yearSel.addEventListener("change", loadAnalytics);
            monthSel.addEventListener("change", loadAnalytics);
            btnReset.addEventListener("click", function() {
                yearSel.value = <?= $yearNow; ?>;
                monthSel.value = 11;
                loadAnalytics();
            });

            loadAnalytics();
        })();
    </script>
</body>

</html>