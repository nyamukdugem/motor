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

                <!-- KPI tambahan -->
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-2">
                                <div class="small text-muted">Biaya / Km</div>
                                <div class="fw-bold" id="valCostPerKm">-</div>
                                <div class="badge bg-light text-secondary mt-1">Efisiensi biaya</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-2">
                                <div class="small text-muted">Efisiensi BBM</div>
                                <div class="fw-bold" id="valAvgKmpl">-</div>
                                <div class="badge bg-light text-secondary mt-1">Rata-rata km/l</div>
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

                <!-- Terakhir (Minyak, Oli, Service) -->
                <div class="card border-0 shadow-sm mt-2">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold text-uppercase text-muted">Terakhir</span>
                        <span class="badge bg-light text-secondary">
                            <i class="bi bi-clock-history me-1"></i> Update terbaru
                        </span>
                    </div>
                    <div class="card-body small">

                        <!-- Minyak -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold">Isi Minyak</div>
                                <div class="text-muted">Pengisian BBM terakhir</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold" id="valLastMinyak">-</div>
                            </div>
                        </div>

                        <hr class="my-2">

                        <!-- Oli -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold">Ganti Oli</div>
                                <div class="text-muted">Info terakhir ganti oli</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold" id="valLastOli">-</div>
                            </div>
                        </div>

                        <hr class="my-2">

                        <!-- Service -->
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">Service</div>
                                <div class="text-muted">Info terakhir service</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold" id="valLastService">-</div>
                            </div>
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

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="section-title mb-0" style="font-size:.8rem;">Riwayat Pengisian</div>
                    <div class="text-muted small"><?= !empty($minyak_list) ? count($minyak_list) . ' data' : ''; ?></div>
                </div>

                <?php
                $yNow = (int)date('Y');
                $mNow = (int)date('n');

                $filterYear  = isset($filterYear) ? (int)$filterYear : $yNow;
                $filterMonth = isset($filterMonth) ? (int)$filterMonth : $mNow;
                ?>

                <?php
                $bulanNama = [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember'
                ];
                ?>


                <div class="card border-0 shadow-sm mb-2">
                    <div class="card-body py-2">
                        <form id="formFilterMinyak" method="get" action="<?= base_url('app'); ?>" class="row g-2 align-items-end">
                            <input type="hidden" name="tab" value="minyak">

                            <div class="col-6">
                                <label class="form-label small mb-1">Tahun</label>
                                <select id="minyakYear" name="y" class="form-select form-select-sm">
                                    <?php for ($yy = $yNow; $yy >= $yNow - 5; $yy--): ?>
                                        <option value="<?= $yy; ?>" <?= $yy === $filterYear ? 'selected' : ''; ?>><?= $yy; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="form-label small mb-1">Bulan</label>
                                <select id="minyakMonth" name="m" class="form-select form-select-sm">
                                    <?php foreach ($bulanNama as $mm => $nm): ?>
                                        <option value="<?= $mm; ?>" <?= $mm === $filterMonth ? 'selected' : ''; ?>><?= $nm; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    Auto filter saat dipilih
                                </div>
                                <a class="small text-decoration-none" href="<?= base_url('app?tab=minyak'); ?>">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <style>
                    :root {
                        --card-r: 16px;
                        --line: rgba(15, 23, 42, .10);
                        --soft: rgba(2, 6, 23, .03);
                    }

                    .fuel-card {
                        border-radius: var(--card-r);
                        border: 1px solid var(--line) !important;
                        box-shadow: 0 10px 26px rgba(2, 6, 23, .06);
                        overflow: hidden;
                        background: #fff;
                    }

                    .fuel-card .topbar {
                        background: linear-gradient(180deg, rgba(37, 99, 235, .08), rgba(37, 99, 235, 0));
                        padding: .85rem 1rem .65rem;
                    }

                    .chip {
                        display: inline-flex;
                        align-items: center;
                        gap: .45rem;
                        padding: .35rem .55rem;
                        border-radius: 999px;
                        border: 1px solid var(--line);
                        background: #fff;
                        color: #475569;
                        font-size: .82rem;
                        line-height: 1;
                        white-space: nowrap;
                    }

                    .money {
                        font-size: 1.55rem;
                        line-height: 1.05;
                        letter-spacing: -.2px;
                    }

                    .subtle {
                        color: #64748b;
                    }

                    .insight {
                        border: 1px solid var(--line);
                        background: var(--soft);
                        border-radius: 14px;
                        padding: .65rem .75rem;
                    }

                    .kpi {
                        font-weight: 800;
                    }

                    .detail-row {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        padding: .55rem 0;
                        border-top: 1px dashed rgba(15, 23, 42, .14);
                        font-size: .92rem;
                    }

                    .detail-row:first-child {
                        border-top: 0;
                        padding-top: 0;
                    }

                    .detail-row .label {
                        color: #64748b;
                    }

                    .btn-ghost {
                        background: transparent;
                        border: 0;
                        padding: .35rem .5rem;
                        border-radius: 10px;
                    }

                    .btn-ghost:hover {
                        background: rgba(2, 6, 23, .06);
                    }

                    .note {
                        border-left: 3px solid rgba(37, 99, 235, .35);
                        padding-left: .65rem;
                    }

                    /* kecilin divider default */
                    .fuel-hr {
                        opacity: .15;
                    }

                    /* biar tombol collapse enak */
                    .detail-toggle {
                        border-radius: 12px;
                        padding: .6rem .75rem;
                    }
                </style>



                <div class="vstack gap-2">
                    <?php if (!empty($minyak_list)): ?>
                        <?php foreach ($minyak_list as $row): ?>
                            <?php
                            $tglLabel = date('d M Y', strtotime($row->tanggal));
                            $odoDisp  = number_format($row->odo_display_km, 0, ',', '.');
                            $odoTot   = number_format((int)$row->odo_total_km, 0, ',', '.');

                            $totalRp = !empty($row->total_uang) ? (int)$row->total_uang : null;
                            $total   = $totalRp ? 'Rp ' . number_format($totalRp, 0, ',', '.') : '-';

                            $literVal = (!empty($row->isi_liter) && (float)$row->isi_liter > 0) ? (float)$row->isi_liter : null;
                            $literTxt = $literVal ? rtrim(rtrim(number_format($literVal, 2, ',', '.'), '0'), ',') . ' L' : null;

                            $hargaPerLiter = null;
                            if ($totalRp && $literVal) $hargaPerLiter = (int)round($totalRp / $literVal);

                            $jarakKm = ($row->jarak_km !== null && $row->jarak_km !== '') ? (float)$row->jarak_km : null;

                            $jarakLabel = null;
                            if ($jarakKm !== null) {
                                $jarakLabel = number_format((int)$jarakKm, 0, ',', '.') . " km";
                                if ($row->selisih_hari === 0) $jarakLabel .= " • hari sama";
                                elseif ($row->selisih_hari === 1) $jarakLabel .= " • 1 hari";
                                elseif ($row->selisih_hari > 1) $jarakLabel .= " • " . (int)$row->selisih_hari . " hari";
                            }

                            $bbm = !empty($row->jenis_bbm) ? html_escape($row->jenis_bbm) : 'BBM';

                            // ===== Indikator hemat/boros =====
                            $kmPerLiter = null;
                            $rpPerKm    = null;
                            $iritLabel  = null;
                            $iritClass  = 'bg-light text-secondary border';

                            if ($jarakKm && $literVal) {
                                $kmPerLiter = $jarakKm / $literVal;
                                if ($totalRp) $rpPerKm = $totalRp / $jarakKm;

                                if ($kmPerLiter >= 40) {
                                    $iritLabel = 'Hemat';
                                    $iritClass = 'bg-success-subtle text-success border';
                                } elseif ($kmPerLiter >= 30) {
                                    $iritLabel = 'Normal';
                                    $iritClass = 'bg-primary-subtle text-primary border';
                                } else {
                                    $iritLabel = 'Boros';
                                    $iritClass = 'bg-danger-subtle text-danger border';
                                }
                            }

                            // collapse default open kalau ada catatan / sisa minyak
                            $openDetail = (!empty($row->catatan) || ($row->sisa_minyak_batang !== null && $row->sisa_minyak_batang !== ''));
                            ?>

                            <div class="card fuel-card"
                                data-row="minyak" data-id="<?= (int)$row->id; ?>"
                                data-json='<?= html_escape(json_encode([
                                                'id' => (int)$row->id,
                                                'motor_id' => (int)$row->motor_id,
                                                'tanggal' => $row->tanggal,
                                                'odo_display_km' => (int)$row->odo_display_km,
                                                'sisa_minyak_batang' => $row->sisa_minyak_batang !== null ? (int)$row->sisa_minyak_batang : null,
                                                'jenis_bbm' => $row->jenis_bbm,
                                                'isi_liter' => $row->isi_liter,
                                                'total_uang' => $row->total_uang,
                                                'lokasi_label' => $row->lokasi_label,
                                                'latitude' => $row->latitude,
                                                'longitude' => $row->longitude,
                                                'catatan' => $row->catatan,
                                            ], JSON_UNESCAPED_UNICODE)); ?>'>

                                <!-- TOP BAR -->
                                <div class="topbar">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <div class="fw-semibold"><?= $tglLabel; ?></div>
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <i class="bi bi-fuel-pump me-1"></i><?= $bbm; ?>
                                                </span>
                                            </div>

                                            <?php if (!empty($row->lokasi_label)): ?>
                                                <div class="small subtle mt-1 text-truncate">
                                                    <i class="bi bi-geo-alt me-1"></i><?= html_escape($row->lokasi_label); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="dropdown">
                                            <button class="btn-ghost" type="button" data-bs-toggle="dropdown" aria-label="Menu">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item js-edit-minyak" type="button">
                                                        <i class="bi bi-pencil-square me-2"></i>Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger js-delete-minyak" type="button">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- BODY -->
                                <div class="card-body p-3">

                                    <!-- HERO TOTAL -->
                                    <div class="small subtle">Total isi</div>
                                    <div class="money fw-bold text-primary mt-1"><?= $total; ?></div>

                                    <!-- CHIPS -->
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <?php if ($literTxt): ?>
                                            <span class="chip"><i class="bi bi-droplet"></i><?= html_escape($literTxt); ?></span>
                                        <?php endif; ?>

                                        <?php if ($hargaPerLiter): ?>
                                            <span class="chip"><i class="bi bi-tag"></i>Rp/L <?= number_format($hargaPerLiter, 0, ',', '.'); ?></span>
                                        <?php endif; ?>

                                        <?php if ($jarakLabel): ?>
                                            <span class="chip"><i class="bi bi-speedometer2"></i><?= html_escape($jarakLabel); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- INSIGHT (hemat/normal/boros) -->
                                    <?php if ($iritLabel && $kmPerLiter !== null): ?>
                                        <div class="insight mt-3">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge <?= $iritClass; ?>">
                                                        <i class="bi bi-graph-up-arrow me-1"></i><?= $iritLabel; ?>
                                                    </span>
                                                    <span class="small subtle">Efisiensi</span>
                                                </div>

                                                <div class="small">
                                                    <span class="kpi"><?= rtrim(rtrim(number_format($kmPerLiter, 1, ',', '.'), '0'), ','); ?></span> km/L
                                                </div>
                                            </div>

                                            <?php if ($rpPerKm !== null): ?>
                                                <div class="small subtle mt-1">
                                                    Biaya per km: <span class="fw-semibold text-dark">Rp <?= number_format((int)round($rpPerKm), 0, ',', '.'); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <hr class="my-3 fuel-hr">

                                    <!-- DETAIL COLLAPSE -->
                                    <button class="btn btn-light border w-100 d-flex align-items-center justify-content-between detail-toggle"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#minyakDetail<?= (int)$row->id; ?>"
                                        aria-expanded="<?= $openDetail ? 'true' : 'false'; ?>">
                                        <span class="fw-semibold">
                                            <i class="bi bi-info-circle me-2"></i>Detail Odo & Sisa
                                        </span>
                                        <i class="bi bi-chevron-down"></i>
                                    </button>

                                    <div class="collapse <?= $openDetail ? 'show' : ''; ?> mt-2" id="minyakDetail<?= (int)$row->id; ?>">
                                        <div class="px-1">

                                            <div class="detail-row">
                                                <span class="label">Odo (display)</span>
                                                <span class="fw-semibold"><?= $odoDisp; ?> km</span>
                                            </div>

                                            <div class="detail-row">
                                                <span class="label">Odo (total)</span>
                                                <span class="fw-semibold"><?= $odoTot; ?> km</span>
                                            </div>

                                            <?php if ($row->sisa_minyak_batang !== null && $row->sisa_minyak_batang !== ''): ?>
                                                <div class="detail-row">
                                                    <span class="label">Sisa minyak</span>
                                                    <span class="fw-semibold"><?= (int)$row->sisa_minyak_batang; ?> batang</span>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                    <?php if (!empty($row->catatan)): ?>
                                        <div class="note small subtle mt-3 fst-italic">
                                            “<?= html_escape($row->catatan); ?>”
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-light border small mb-0">
                            Belum ada riwayat pengisian minyak untuk periode ini.
                        </div>
                    <?php endif; ?>
                </div>




            </div>
        </section>

        <div class="modal fade" id="modalEditMinyak" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form id="formEditMinyak" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Pengisian Minyak</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editMinyakId">
                        <input type="hidden" name="motor_id" value="<?= (int)$motor->id; ?>">

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Tanggal</label>
                                <input type="date" class="form-control form-control-sm" name="tanggal" id="editMinyakTanggal" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">ODO (display)</label>
                                <input type="number" class="form-control form-control-sm" name="odo_display_km" id="editMinyakOdo" required>
                            </div>

                            <div class="col-6">
                                <label class="form-label small">Jenis BBM</label>
                                <input type="text" class="form-control form-control-sm" name="jenis_bbm" id="editMinyakBbm" placeholder="Pertalite/Pertamax">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Isi (liter)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="isi_liter" id="editMinyakLiter">
                            </div>

                            <div class="col-6">
                                <label class="form-label small">Total Uang</label>
                                <input type="number" class="form-control form-control-sm" name="total_uang" id="editMinyakTotal">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Sisa (batang)</label>
                                <input type="number" class="form-control form-control-sm" name="sisa_minyak_batang" id="editMinyakSisa" min="0" max="10">
                            </div>

                            <div class="col-12">
                                <label class="form-label small">Lokasi</label>
                                <input type="text" class="form-control form-control-sm" name="lokasi_label" id="editMinyakLokasi" placeholder="SPBU ...">
                                <input type="hidden" name="latitude" id="editMinyakLat">
                                <input type="hidden" name="longitude" id="editMinyakLng">
                            </div>

                            <div class="col-12">
                                <label class="form-label small">Catatan</label>
                                <input type="text" class="form-control form-control-sm" name="catatan" id="editMinyakCatatan" placeholder="Opsional">
                            </div>
                        </div>

                        <div class="alert alert-light border small mt-3 mb-0">
                            Odo total akan dihitung ulang otomatis.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>


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
        // ===== Helper Tanggal (Indonesia) untuk JS =====
        const Tgl = (() => {
            const bulan = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];
            const hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

            // Parse aman untuk 'YYYY-MM-DD' -> Date (lokal), anti offset UTC
            function parseYmd(ymd) {
                if (!ymd || typeof ymd !== "string") return null;
                const m = ymd.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) return null;
                const y = Number(m[1]);
                const mo = Number(m[2]) - 1;
                const d = Number(m[3]);
                const dt = new Date(y, mo, d, 12, 0, 0); // jam 12 biar aman DST/offset
                return Number.isNaN(dt.getTime()) ? null : dt;
            }

            function tglIndo(ymd, withDay = false) {
                const dt = parseYmd(ymd);
                if (!dt) return "-";
                const d = dt.getDate();
                const m = dt.getMonth();
                const y = dt.getFullYear();
                const base = `${String(d).padStart(2, "0")} ${bulan[m]} ${y}`;
                return withDay ? `${hari[dt.getDay()]}, ${base}` : base;
            }

            function bulanTahun(year, month) {
                const y = Number(year);
                const m = Number(month);
                if (!y || !m || m < 1 || m > 12) return "-";
                return `${bulan[m - 1]} ${y}`;
            }

            function waktuRelatif(ymd) {
                const dt = parseYmd(ymd);
                if (!dt) return "-";

                // bandingin dari "hari" (bukan jam) supaya stabil
                const now = new Date();
                const a = new Date(dt.getFullYear(), dt.getMonth(), dt.getDate());
                const b = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const diffDays = Math.round((b - a) / 86400000);

                if (diffDays === 0) return "Hari ini";
                if (diffDays === 1) return "Kemarin";
                if (diffDays > 1 && diffDays < 7) return `${diffDays} hari lalu`;
                if (diffDays >= 7 && diffDays < 30) return `${Math.floor(diffDays / 7)} minggu lalu`;
                if (diffDays >= 30 && diffDays < 365) return `${Math.floor(diffDays / 30)} bulan lalu`;
                if (diffDays >= 365) return `${Math.floor(diffDays / 365)} tahun lalu`;

                // tanggal masa depan
                if (diffDays === -1) return "Besok";
                if (diffDays < -1) return `${Math.abs(diffDays)} hari lagi`;
                return "-";
            }

            return {
                tglIndo,
                bulanTahun,
                waktuRelatif,
                parseYmd
            };
        })();
    </script>


    <script>
        // Switch page (tetap)
        (function() {
            const navLinks = document.querySelectorAll(".bottom-nav .nav-link");
            const pages = document.querySelectorAll(".page");

            function showPage(id) {
                pages.forEach((p) => p.classList.remove("active"));
                document.getElementById(id)?.classList.add("active");
                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }

            navLinks.forEach((btn) => {
                btn.addEventListener("click", () => {
                    navLinks.forEach((b) => b.classList.remove("active"));
                    btn.classList.add("active");
                    showPage(btn.getAttribute("data-target"));
                });
            });
        })();

        // Ambil lokasi GPS (tetap)
        function getLocation(type) {
            if (!navigator.geolocation) {
                alert("Perangkat ini tidak mendukung GPS (geolocation).");
                return;
            }
            const map = {
                minyak: {
                    label: document.getElementById("minyakLocationLabel"),
                    lat: document.getElementById("minyakLat"),
                    lng: document.getElementById("minyakLng"),
                },
                service: {
                    label: document.getElementById("serviceLocationLabel"),
                    lat: document.getElementById("serviceLat"),
                    lng: document.getElementById("serviceLng"),
                },
                oli: {
                    label: document.getElementById("oliLocationLabel"),
                    lat: document.getElementById("oliLat"),
                    lng: document.getElementById("oliLng"),
                },
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

        // Analytics dari backend (UPGRADED)
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

            // (opsional) kalau nanti kamu tambah elemen KPI baru di HTML, tinggal aktif:
            const elCostPerKm = document.getElementById("valCostPerKm"); // contoh id
            const elAvgKmpl = document.getElementById("valAvgKmpl"); // contoh id
            const elLastOli = document.getElementById("valLastOli"); // contoh id
            const elLastService = document.getElementById("valLastService"); // contoh id
            const elLastMinyak = document.getElementById("valLastMinyak");


            const API_URL = "<?= base_url('app/get_analytics'); ?>";

            // cache per (tahun-bulan)
            const cache = new Map();
            let aborter = null;

            const now = new Date();
            const defaultYear = <?= (int)$yearNow; ?>;
            const defaultMonth = now.getMonth() + 1;

            function toInt(v, fallback = 0) {
                const n = Number(v);
                return Number.isFinite(n) ? n : fallback;
            }

            function fmtNumber(v) {
                const n = toInt(v, null);
                if (n === null) return "-";
                return n.toLocaleString("id-ID");
            }

            function formatRupiah(v) {
                const n = toInt(v, 0);
                return "Rp " + n.toLocaleString("id-ID");
            }

            function setText(el, text) {
                if (!el) return;
                el.textContent = text;
            }

            function showEmpty(label = "-") {
                dashAnalytics.classList.add("d-none");
                dashEmpty.classList.remove("d-none");
                setText(labelPeriode, "Periode: " + (label || "-"));

                // reset ui ringkas
                setText(valTotal, "Rp 0");
                setText(valTransaksi, "0 Transaksi");
                setText(valMinyak, "Rp 0");
                setText(valMinyakCount, "0x Isi");
                setText(valServiceOli, "Rp 0");
                setText(valServiceOliCount, "0x");

                setText(tblMinyakTotal, "Rp 0");
                setText(tblMinyakCount, "0x");
                setText(tblServiceTotal, "Rp 0");
                setText(tblServiceCount, "0x");
                setText(tblOliTotal, "Rp 0");
                setText(tblOliCount, "0x");
                setText(tblKmRange, "-");

                setText(kmUsedLabel, "0 km");
                setText(kmStartLabel, "Awal: -");
                setText(kmEndLabel, "Akhir: -");
                if (kmProgress) kmProgress.style.width = "0%";

                // optional KPI
                setText(elCostPerKm, "-");
                setText(elAvgKmpl, "-");
                setText(elLastOli, "-");
                setText(elLastService, "-");
            }

            function render(data) {
                if (!data || data.error) {
                    showEmpty("-");
                    return;
                }

                // indikator "ada data"
                const total = toInt(data.total, 0);
                const kmUsed = toInt(data?.km?.used, 0);
                const trx = toInt(data.total_transaksi, 0);
                const hasAny = total > 0 || kmUsed > 0 || trx > 0;

                if (!hasAny) {
                    showEmpty(data.label || "-");
                    return;
                }

                dashAnalytics.classList.remove("d-none");
                dashEmpty.classList.add("d-none");

                setText(labelPeriode, "Periode: " + (data.label || "-"));

                const minyakTotal = toInt(data?.minyak?.total, 0);
                const minyakCount = toInt(data?.minyak?.count, 0);
                const serviceTotal = toInt(data?.service?.total, 0);
                const serviceCount = toInt(data?.service?.count, 0);
                const oliTotal = toInt(data?.oli?.total, 0);
                const oliCount = toInt(data?.oli?.count, 0);

                const totalTrans = trx || (minyakCount + serviceCount + oliCount);

                setText(valTotal, formatRupiah(total));
                setText(valTransaksi, fmtNumber(totalTrans) + " Transaksi");

                setText(valMinyak, formatRupiah(minyakTotal));
                setText(valMinyakCount, fmtNumber(minyakCount) + "x Isi");

                const serviceOliTotal = serviceTotal + oliTotal;
                const serviceOliCount = serviceCount + oliCount;
                setText(valServiceOli, formatRupiah(serviceOliTotal));
                setText(valServiceOliCount, fmtNumber(serviceOliCount) + "x");

                setText(tblMinyakTotal, formatRupiah(minyakTotal));
                setText(tblMinyakCount, fmtNumber(minyakCount) + "x");

                setText(tblServiceTotal, formatRupiah(serviceTotal));
                setText(tblServiceCount, fmtNumber(serviceCount) + "x");

                setText(tblOliTotal, formatRupiah(oliTotal));
                setText(tblOliCount, fmtNumber(oliCount) + "x");

                // KM
                const kmStart = data?.km?.start ?? null;
                const kmEnd = data?.km?.end ?? null;

                if (kmStart !== null && kmEnd !== null && kmEnd >= kmStart) {
                    setText(tblKmRange, fmtNumber(kmStart) + " → " + fmtNumber(kmEnd) + " km");
                    setText(kmUsedLabel, fmtNumber(kmUsed) + " km");
                    setText(kmStartLabel, "Awal: " + fmtNumber(kmStart) + " km");
                    setText(kmEndLabel, "Akhir: " + fmtNumber(kmEnd) + " km");

                    // progress: default target 1000 km/bulan (kamu bisa bikin configurable)
                    const targetKm = 1000;
                    const percent = targetKm > 0 ? Math.min((kmUsed / targetKm) * 100, 100) : 0;
                    if (kmProgress) kmProgress.style.width = percent.toFixed(0) + "%";
                } else {
                    setText(tblKmRange, "-");
                    setText(kmUsedLabel, "0 km");
                    setText(kmStartLabel, "Awal: -");
                    setText(kmEndLabel, "Akhir: -");
                    if (kmProgress) kmProgress.style.width = "0%";
                }

                // KPI opsional (kalau elemen HTML sudah ada)
                const costPerKm = data?.kpi?.cost_per_km ?? null;
                const avgKmpl = data?.kpi?.avg_km_per_l ?? null;

                if (elCostPerKm) {
                    setText(elCostPerKm, costPerKm !== null ? (formatRupiah(costPerKm) + " / km") : "-");
                }
                if (elAvgKmpl) {
                    setText(elAvgKmpl, avgKmpl !== null ? (String(avgKmpl).replace(".", ",") + " km/l") : "-");
                }

                // last info opsional
                if (elLastOli) {
                    const lo = data?.last?.oli;
                    setText(
                        elLastOli,
                        lo ?
                        `${Tgl.tglIndo(lo.tanggal)} • ${fmtNumber(lo.km_since)} km lalu` :
                        "-"
                    );
                }

                if (elLastService) {
                    const ls = data?.last?.service;
                    setText(
                        elLastService,
                        ls ?
                        `${Tgl.tglIndo(ls.tanggal)} • ${fmtNumber(ls.km_since)} km lalu` :
                        "-"
                    );
                }

                // Terakhir isi minyak
                if (elLastMinyak) {
                    const lm = data?.last?.minyak;
                    setText(
                        elLastMinyak,
                        lm ?
                        `${Tgl.tglIndo(lm.tanggal)} • ${fmtNumber(lm.km_since)} km lalu` :
                        "-"
                    );
                }


            }


            async function loadAnalytics() {
                const tahun = String(yearSel.value || "");
                const bulan = String(monthSel.value || "");

                if (!tahun || !bulan) {
                    showEmpty("-");
                    return;
                }

                const cacheKey = `${motorId}-${tahun}-${bulan}`;
                if (cache.has(cacheKey)) {
                    render(cache.get(cacheKey));
                    return;
                }

                const url = `${API_URL}?motor_id=${encodeURIComponent(motorId)}&tahun=${encodeURIComponent(tahun)}&bulan=${encodeURIComponent(bulan)}`;

                // abort request sebelumnya biar gak balapan
                if (aborter) aborter.abort();
                aborter = new AbortController();

                // loading state kecil
                setText(labelPeriode, "Periode: memuat...");

                try {
                    const res = await fetch(url, {
                        signal: aborter.signal,
                        headers: {
                            "Accept": "application/json"
                        }
                    });
                    const data = await res.json();

                    cache.set(cacheKey, data);
                    render(data);
                } catch (err) {
                    if (err?.name === "AbortError") return;
                    console.error(err);
                    showEmpty("-");
                }
            }

            yearSel.addEventListener("change", loadAnalytics);
            monthSel.addEventListener("change", loadAnalytics);

            btnReset.addEventListener("click", function() {
                yearSel.value = <?= $yearNow; ?>;
                monthSel.value = (new Date()).getMonth() + 1; // bulan sekarang
                loadAnalytics();
            });


            // init default: set bulan sekarang kalau select masih default hardcode
            if (!monthSel.value) monthSel.value = String(defaultMonth);
            if (!yearSel.value) yearSel.value = String(defaultYear);

            loadAnalytics();
        })();
    </script>

    <script>
        (function() {
            const modalEl = document.getElementById("modalEditMinyak");
            const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

            function getCard(btn) {
                return btn.closest('[data-row="minyak"]');
            }

            function parseJsonFromCard(card) {
                const raw = card.getAttribute("data-json");
                try {
                    return JSON.parse(raw);
                } catch (e) {
                    return null;
                }
            }

            // Toggle detail
            document.addEventListener("click", function(e) {
                const t = e.target.closest(".js-toggle-detail");
                if (!t) return;
                const card = getCard(t);
                if (!card) return;
                const detail = card.querySelector(".js-detail");
                if (!detail) return;

                const isHidden = detail.classList.contains("d-none");
                detail.classList.toggle("d-none");
                t.innerHTML = isHidden ?
                    '<i class="bi bi-chevron-up me-1"></i>Sembunyikan' :
                    '<i class="bi bi-chevron-down me-1"></i>Detail';
            });

            // Edit
            document.addEventListener("click", function(e) {
                const btn = e.target.closest(".js-edit-minyak");
                if (!btn) return;

                const card = getCard(btn);
                const data = card ? parseJsonFromCard(card) : null;
                if (!data || !modal) return;

                // isi form
                document.getElementById("editMinyakId").value = data.id || "";
                document.getElementById("editMinyakTanggal").value = data.tanggal || "";
                document.getElementById("editMinyakOdo").value = data.odo_display_km ?? "";
                document.getElementById("editMinyakBbm").value = data.jenis_bbm ?? "";
                document.getElementById("editMinyakLiter").value = data.isi_liter ?? "";
                document.getElementById("editMinyakTotal").value = data.total_uang ?? "";
                document.getElementById("editMinyakSisa").value = data.sisa_minyak_batang ?? "";
                document.getElementById("editMinyakLokasi").value = data.lokasi_label ?? "";
                document.getElementById("editMinyakLat").value = data.latitude ?? "";
                document.getElementById("editMinyakLng").value = data.longitude ?? "";
                document.getElementById("editMinyakCatatan").value = data.catatan ?? "";

                modal.show();
            });

            // Submit edit
            const form = document.getElementById("formEditMinyak");
            if (form) {
                form.addEventListener("submit", async function(e) {
                    e.preventDefault();

                    const url = "<?= base_url('app/update_minyak'); ?>";
                    const fd = new FormData(form);

                    try {
                        const res = await fetch(url, {
                            method: "POST",
                            body: fd,
                            headers: {
                                "Accept": "application/json"
                            }
                        });
                        const out = await res.json();

                        if (!out.ok) {
                            alert(out.msg || "Gagal menyimpan.");
                            return;
                        }

                        // refresh simpel: reload page (paling aman karena list hitung jarak/selisih hari)
                        location.reload();
                    } catch (err) {
                        console.error(err);
                        alert("Terjadi error saat menyimpan.");
                    }
                });
            }

            // Hapus
            document.addEventListener("click", async function(e) {
                const btn = e.target.closest(".js-delete-minyak");
                if (!btn) return;

                const card = getCard(btn);
                const data = card ? parseJsonFromCard(card) : null;
                if (!data) return;

                if (!confirm("Hapus catatan pengisian ini?")) return;

                const url = "<?= base_url('app/delete_minyak'); ?>";
                const fd = new FormData();
                fd.append("id", data.id);
                fd.append("motor_id", data.motor_id);

                try {
                    const res = await fetch(url, {
                        method: "POST",
                        body: fd,
                        headers: {
                            "Accept": "application/json"
                        }
                    });
                    const out = await res.json();

                    if (!out.ok) {
                        alert(out.msg || "Gagal menghapus.");
                        return;
                    }

                    // remove card dari DOM
                    card.remove();
                } catch (err) {
                    console.error(err);
                    alert("Terjadi error saat menghapus.");
                }
            });
        })();
    </script>

    <script>
        (function() {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get("tab");
            if (!tab) return;

            const map = {
                dashboard: "page-dashboard",
                minyak: "page-minyak",
                service: "page-service",
                oli: "page-oli",
            };
            const target = map[tab];
            if (!target) return;

            // set nav active
            document.querySelectorAll(".bottom-nav .nav-link").forEach(a => {
                a.classList.toggle("active", a.getAttribute("data-target") === target);
            });

            // show page
            document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
            document.getElementById(target)?.classList.add("active");
        })();
    </script>

    <script>
        (function() {
            const f = document.getElementById("formFilterMinyak");
            if (!f) return;
            const y = document.getElementById("minyakYear");
            const m = document.getElementById("minyakMonth");
            const submit = () => f.submit();

            y?.addEventListener("change", submit);
            m?.addEventListener("change", submit);
        })();
    </script>




</body>

</html>