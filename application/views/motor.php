<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
    <title>MotorTrack Pro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0d6efd;
            --bg: #f4f7fa;
            --surface: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: #334155;
            font-size: 0.9rem;
            padding-bottom: 80px;
        }

        /* --- Compact Header --- */
        .top-bar {
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
            color: white;
            padding: 0.8rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* --- Horizontal Quick Stats --- */
        .stats-scroller {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 1rem 1.25rem;
            scrollbar-width: none;
        }

        .stats-scroller::-webkit-scrollbar {
            display: none;
        }

        .stat-item {
            min-width: 140px;
            background: white;
            padding: 0.75rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #edf2f7;
        }

        /* --- Compact Maintenance Cards --- */
        .health-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 0 1.25rem;
        }

        .health-card {
            background: white;
            padding: 0.85rem;
            border-radius: 12px;
            border: 1px solid #edf2f7;
        }

        .progress-tiny {
            height: 4px;
            background: #e2e8f0;
            margin-top: 8px;
            border-radius: 2px;
        }

        /* --- Compact History List --- */
        .section-title {
            padding: 1rem 1.25rem 0.5rem;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }

        .history-item {
            background: white;
            margin: 0 1.25rem 8px;
            padding: 0.75rem;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #edf2f7;
        }

        /* --- Floating Action --- */
        .fab-btn {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #1e293b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border: none;
            z-index: 1050;
        }

        .fab-menu {
            position: fixed;
            bottom: 145px;
            right: 20px;
            display: none;
            flex-direction: column;
            gap: 8px;
            z-index: 1050;
        }

        .fab-menu.show {
            display: flex;
        }

        .fab-option {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            text-align: right;
        }

        /* --- Navbar --- */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 0.5rem 0 1.2rem;
            border-top: 1px solid #e2e8f0;
        }

        .nav-link-custom {
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.7rem;
            text-align: center;
        }

        .nav-link-custom.active {
            color: var(--primary);
        }

        .nav-link-custom i {
            font-size: 1.2rem;
            display: block;
        }

        /* Tambahkan atau pastikan style ini ada */
        #riwayatContainer {
            padding: 0 1.25rem;
            /* Menyamakan margin dengan dashboard */
        }

        /* Penyesuaian agar kartu riwayat di halaman khusus terlihat lebih elegan */
        #page-history .history-item {
            margin-left: 0;
            margin-right: 0;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="top-bar shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-bicycle text-primary fs-5"></i>
            </div>
            <div>
                <div class="fw-bold lh-1" style="font-size: 1rem;">
                    <?= strtoupper(html_escape($motor->nama)); ?>
                </div>
                <div class="small fw-normal text-white-50" style="font-size: 0.75rem; letter-spacing: 1px;">
                    <?= strtoupper(html_escape($motor->plat_nomor)); ?>
                </div>
            </div>
        </div>

        <a href="javascript:void(0)" onclick="showPage('settings', this)" class="text-white text-decoration-none">
            <div class="position-relative">
                <i class="bi bi-person-circle fs-4"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </div>
        </a>
    </div>

    <div id="page-dashboard" class="page-view">
        <div class="px-3 mb-2">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-2">
                    <div class="row g-2">
                        <div class="col-7">
                            <select id="dashFilterMonth" class="form-select form-select-sm border-0 bg-light" onchange="loadDashboardData()">
                                <option value="0">Semua Bulan</option>
                                <?php
                                $m_list = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                foreach ($m_list as $idx => $m_name) {
                                    $selected = (date('n') == ($idx + 1)) ? 'selected' : '';
                                    echo "<option value='" . ($idx + 1) . "' $selected>$m_name</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-5">
                            <select id="dashFilterYear" class="form-select form-select-sm border-0 bg-light" onchange="loadDashboardData()">
                                <option value="0">Semua Tahun</option> <?php foreach ($available_years as $y): ?>
                                    <option value="<?= $y; ?>" <?= ($y == date('Y')) ? 'selected' : ''; ?>><?= $y; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-scroller">
            <div class="stat-item">
                <div class="small text-muted">Total Biaya</div>
                <div class="fw-bold text-primary" id="dashTotalBiaya">Rp 0</div>
            </div>
            <div class="stat-item">
                <div class="small text-muted">Efisiensi</div>
                <div class="fw-bold" id="dashEfisiensi">0 <small>km/L</small></div>
            </div>
            <div class="stat-item">
                <div class="small text-muted">Total KM</div>
                <div class="fw-bold" id="dashTotalOdo">0</div>
            </div>
        </div>

        <div class="px-3 mb-3">
            <div class="card border-0 shadow-sm" style="border-radius: 18px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="font-size: 0.85rem;">Tren Pengeluaran</h6>
                        <i class="bi bi-graph-up text-primary"></i>
                    </div>
                    <div style="height: 180px;">
                        <canvas id="expenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">Kesehatan Motor</div>
        <div class="health-grid" id="dashHealthGrid">
            <div class="text-center p-3 w-100">
                <div class="spinner-border spinner-border-sm text-primary"></div>
            </div>
        </div>

        <div class="section-title">
            <span>Riwayat Terakhir</span>
            <a href="#" class="text-primary text-decoration-none small" onclick="showPage('history')">Semua</a>
        </div>
        <div id="dashRecentHistory">
        </div>
    </div>


    <div id="page-history" class="page-view d-none">

        <div class="section-title">Riwayat</div>

        <div class="px-3">

            <div id="totalRiwayatContainer" class="d-none">
                <div class="card border-0 shadow-sm mb-3 text-white" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);">
                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75 d-block" style="font-size: 0.7rem;">Total Pengeluaran</small>
                            <div class="fw-bold h5 mb-0" id="textTotalBiaya">Rp 0</div>
                        </div>
                        <div class="text-end">
                            <small class="opacity-75 d-block" style="font-size: 0.7rem;" id="textTotalItem">0 Transaksi</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px; background: #fff;">
                <div class="card-body p-2">
                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <select id="histFilterMonth" class="form-select form-select-sm border-0 bg-light">
                                <option value="0">Semua Bulan</option>
                                <?php
                                $m_list = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                foreach ($m_list as $idx => $m_name) echo "<option value='" . ($idx + 1) . "'>$m_name</option>";
                                ?>
                            </select>
                        </div>
                        <div class="col-5">
                            <select id="histFilterYear" class="form-select form-select-sm border-0 bg-light">
                                <?php foreach ($available_years as $y) echo "<option value='$y'>$y</option>"; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-9">
                            <select id="histFilterType" class="form-select form-select-sm border-0 bg-light">
                                <option value="all">Semua Catatan</option>
                                <option value="bensin">Hanya Bensin</option>
                                <option value="oli">Hanya Ganti Oli</option>
                                <option value="servis">Hanya Servis</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-primary btn-sm w-100 rounded-3 shadow-sm" onclick="loadRiwayat()">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="riwayatContainer"></div>
    </div>

    <div id="page-maintenance" class="page-view d-none">
        <div class="section-title">Rencana Perawatan</div>

        <div class="px-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Status Komponen</h5>

            </div>

            <div id="maintenanceContainer">
            </div>

            <div class="mt-4">
                <div class="section-title px-0 mb-2">Tips Perawatan</div>
                <div class="card border-0 bg-light" style="border-radius: 15px;">
                    <div class="card-body p-3 small text-muted">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                        Komponen berwarna <span class="text-danger fw-bold">Merah</span> menandakan sudah melewati batas interval dan sangat disarankan untuk segera diganti atau diservis.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="page-settings" class="page-view d-none">
        <div class="px-3">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-bicycle fs-2"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" id="setNamaMotor"><?= $motor->nama; ?></h6>
                            <p class="text-muted small mb-0"><?= $motor->plat_nomor; ?></p>
                            <span class="badge bg-success-subtle text-success mt-1" style="font-size: 0.6rem;">Status: Terhubung</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title px-0 mb-2">Kendaraan</div>
            <div class="list-group border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 py-3" onclick="openModalEditMotor()">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-pencil-square me-3 text-primary"></i>
                        <span>Edit Profil Motor</span>
                    </div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </button>
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 py-3" onclick="openModal('master_komponen')">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-gear-wide-connected me-3 text-primary"></i>
                        <span>Kelola Master Komponen</span>
                    </div>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </button>
            </div>

            <div class="section-title px-0 mb-2">Preferensi & Data</div>
            <div class="list-group border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 py-3" onclick="exportData()">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-excel me-3 text-success"></i>
                        <span>Ekspor Riwayat (Excel)</span>
                    </div>
                    <i class="bi bi-download text-muted small"></i>
                </button>
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 py-3 text-danger" onclick="hapusSemuaData()">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-trash3 me-3"></i>
                        <span>Reset Semua Data</span>
                    </div>
                </button>
            </div>

            <div class="text-center mt-5">
                <small class="text-muted">MotorTrack Pro v1.0.2</small><br>
                <small class="text-muted-50" style="font-size: 0.7rem;">&copy; 2026 Developer Motor</small>
            </div>
        </div>
    </div>

    <div class="fab-menu" id="fabMenu">
        <button class="fab-option border-0" onclick="openModal('bensin')">Isi Bensin</button>
        <button class="fab-option border-0" onclick="openModal('oli')">Ganti Oli</button>
        <button class="fab-option border-0" onclick="openModal('servis')">Servis Rutin</button>
        <button class="fab-option border-0" onclick="openModal('part')">Update Kondisi Part</button>
        <button class="fab-option border-0 bg-info text-white" onclick="openModal('master_komponen')">
            <i class="bi bi-plus-circle me-1"></i> Master Komponen
        </button>
    </div>

    <button class="fab-btn" onclick="toggleFab()">
        <i class="bi bi-plus-lg fs-4" id="fabIcon"></i>
    </button>

    <nav class="bottom-nav">
        <button class="nav-link-custom active border-0 bg-transparent" onclick="showPage('dashboard', this)">
            <i class="bi bi-speedometer2"></i>Beranda
        </button>
        <button class="nav-link-custom border-0 bg-transparent" onclick="showPage('history', this)">
            <i class="bi bi-clock-history"></i>Riwayat
        </button>
        <button class="nav-link-custom border-0 bg-transparent" onclick="showPage('maintenance', this)">
            <i class="bi bi-calendar-check"></i>Jadwal
        </button>
        <button class="nav-link-custom border-0 bg-transparent" onclick="showPage('settings', this)">
            <i class="bi bi-gear"></i>Set
        </button>
    </nav>


    <div class="modal fade" id="modalDinamis" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="modalTitle">Tambah Data</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formDinamis">
                        <input type="hidden" name="motor_id" value="<?= $motor->id; ?>">
                        <div id="formContent">
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary fw-bold p-2" style="border-radius: 12px;" id="btnSimpan">
                                <span id="btnText">Simpan Catatan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function toggleFab() {
            const menu = document.getElementById('fabMenu');
            const icon = document.getElementById('fabIcon');

            // Validasi agar tidak error jika elemen tidak ditemukan
            if (menu && icon) {
                menu.classList.toggle('show');
                icon.classList.toggle('bi-plus-lg');
                icon.classList.toggle('bi-x-lg');
            }
        }
    </script>

    <script>
        const motorId = <?= $motor->id; ?>;
        const motorOdoSekarang = <?= $motor->odo_current_km; ?>;

        // Konfigurasi Form Dinamis
        const formTemplates = {
            bensin: {
                title: 'Isi Bensin',
                action: '<?= base_url("app/simpan_minyak"); ?>',
                html: `
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="small fw-bold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                </div>
                <div class="col-6">
                    <label class="small fw-bold">Jenis BBM</label>
                    <select name="jenis_bbm" class="form-select form-select-sm">
                        <option value="Pertalite">Pertalite</option>
                        <option value="Pertamax">Pertamax</option>
                        <option value="Turbo">Turbo</option>
                    </select>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="small fw-bold">KM Display</label>
                    <input type="text" class="form-control form-control-sm mask-number" placeholder="0" required oninput="applyMask(this)">
                    <input type="hidden" name="odo_display_km" id="real_odo" value="${motorOdoSekarang}">
                </div>
                <div class="col-6">
                    <label class="small fw-bold">Sisa (Batang)</label>
                    <select name="sisa_minyak_batang" class="form-select form-select-sm">
                        <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">Full</option>
                    </select>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="small fw-bold">Liter</label>
                    <input type="number" step="0.01" name="isi_liter" class="form-control form-control-sm" placeholder="0.00" required>
                </div>
                <div class="col-6">
                    <label class="small fw-bold">Total Uang (Rp)</label>
                    <input type="text" class="form-control form-control-sm mask-number" placeholder="0" required oninput="applyMask(this)">
                    <input type="hidden" name="total_uang" id="real_uang">
                </div>
            </div>

            <div class="mb-2">
                <label class="small fw-bold">Lokasi / SPBU</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="lokasi_label" class="form-control loc-label" placeholder="Nama lokasi/SPBU">
                    <button class="btn btn-outline-secondary" type="button" onclick="getCurrentLocation(event)">
                        <i class="bi bi-geo-alt"></i>
                    </button>
                </div>
                <input type="hidden" name="latitude" class="loc-lat">
                <input type="hidden" name="longitude" class="loc-lng">
            </div>

            <div class="mb-0">
                <label class="small fw-bold">Catatan</label>
                <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Opsional...">
            </div>
        `
            },
            oli: {
                title: 'Ganti Oli',
                action: '<?= base_url("app/simpan_oli"); ?>',
                html: `
            <div class="mb-2">
                <label class="small fw-bold">Merek Oli</label>
                <input type="text" name="merek_oli" class="form-control form-control-sm" list="oliBrands" placeholder="Ketik merek oli..." required>
                <datalist id="oliBrands">
                    <?php foreach ($oli_brands as $b): ?>
                        <option value="<?= html_escape($b->merek_oli); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="small fw-bold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                </div>
                <div class="col-6">
                    <label class="small fw-bold">KM Saat Ini</label>
                    <input type="text" class="form-control form-control-sm mask-number" placeholder="0" required oninput="applyMask(this)">
                    <input type="hidden" name="odo_display_km" value="${motorOdoSekarang}">
                </div>
            </div>
            <div class="mb-2">
                <label class="small fw-bold">Harga (Rp)</label>
                <input type="text" class="form-control form-control-sm mask-number" placeholder="0" oninput="applyMask(this)">
                <input type="hidden" name="harga">
            </div>
            <div class="mb-2">
                <label class="small fw-bold">Lokasi Bengkel</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="lokasi_label" class="form-control loc-label" placeholder="Nama Bengkel">
                    <button class="btn btn-outline-secondary" type="button" onclick="getCurrentLocation(event)">
                        <i class="bi bi-geo-alt"></i>
                    </button>
                </div>
                <input type="hidden" name="latitude" class="loc-lat">
                <input type="hidden" name="longitude" class="loc-lng">
            </div>
            <div class="mb-0">
                <label class="small fw-bold">Keterangan</label>
                <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Contoh: Oli Mesin + Oli Gardan">
            </div>
        `
            },
            servis: {
                title: 'Catat Servis Rutin',
                action: '<?= base_url("app/simpan_service"); ?>',
                html: `
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="small fw-bold">Tanggal</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="col-6">
                <label class="small fw-bold">KM Saat Ini</label>
                <input type="text" class="form-control form-control-sm mask-number" placeholder="0" required oninput="applyMask(this)">
                <input type="hidden" name="odo_display_km" value="${motorOdoSekarang}">
            </div>
        </div>

        <div class="mb-3">
            <label class="small fw-bold">Pilih Komponen yang Diganti/Servis</label>
            <div class="border rounded-3 p-2 bg-light" style="max-height: 120px; overflow-y: auto;">
                <?php foreach ($ref_komponen as $k): ?>
                <div class="form-check small mb-1">
                    <input class="form-check-input" type="checkbox" name="komponen_ids[]" value="<?= $k->id; ?>" id="chk_<?= $k->id; ?>">
                    <label class="form-check-label" for="chk_<?= $k->id; ?>">
                        <?= html_escape($k->nama_komponen); ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <small class="text-muted" style="font-size: 0.6rem;">* Item yang dicentang akan mereset jadwal ke 100%.</small>
        </div>

        <div class="mb-2">
            <label class="small fw-bold">Biaya Servis (Rp)</label>
            <input type="text" class="form-control form-control-sm mask-number" placeholder="0" oninput="applyMask(this)">
            <input type="hidden" name="harga">
        </div>

        <div class="mb-2">
                <label class="small fw-bold">Lokasi Bengkel</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="lokasi_label" class="form-control loc-label" placeholder="Nama Bengkel">
                    <button class="btn btn-outline-secondary" type="button" onclick="getCurrentLocation(event)">
                        <i class="bi bi-geo-alt"></i>
                    </button>
                </div>
                <input type="hidden" name="latitude" class="loc-lat">
                <input type="hidden" name="longitude" class="loc-lng">
            </div>

        <div class="mb-0">
            <label class="small fw-bold">Catatan Tambahan</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Contoh: Ganti kampas rem, bersihkan injeksi..."></textarea>
        </div>
    `
            },
            part: {
                title: 'Update Kondisi Part',
                action: '<?= base_url("app/simpan_maintenance"); ?>',
                html: `
        <div class="mb-3">
            <label class="small fw-bold">Pilih Komponen</label>
            <select name="komponen_id" class="form-select form-select-sm" required>
                <option value="">-- Pilih Komponen --</option>
                <?php foreach ($ref_komponen as $k): ?>
                    <option value="<?= $k->id; ?>"><?= html_escape($k->nama_komponen); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="small fw-bold">Tanggal Ganti</label>
                <input type="date" name="last_service_date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="col-6">
                <label class="small fw-bold">KM Saat Ganti</label>
                <input type="text" class="form-control form-control-sm mask-number" placeholder="0" required oninput="applyMask(this)">
                <input type="hidden" name="last_service_odo" value="${motorOdoSekarang}">
            </div>
        </div>

        <div class="mb-0">
            <label class="small fw-bold">Status Kondisi</label>
            <div class="d-flex gap-2 mt-1">
                <input type="radio" class="btn-check" name="status" id="part_baru" value="baru" checked>
                <label class="btn btn-outline-success btn-sm flex-fill rounded-pill" for="part_baru">Baru</label>

                <input type="radio" class="btn-check" name="status" id="part_baik" value="baik">
                <label class="btn btn-outline-primary btn-sm flex-fill rounded-pill" for="part_baik">Masih Layak</label>

                <input type="radio" class="btn-check" name="status" id="part_waspada" value="waspada">
                <label class="btn btn-outline-warning btn-sm flex-fill rounded-pill" for="part_waspada">Waspada</label>
            </div>
        </div>
        <div class="mt-3 p-2 bg-light rounded border">
            <small class="text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-info-circle me-1"></i> 
                Input KM terakhir saat part ini diganti/diservis untuk menghitung masa pakai di dashboard.
            </small>
        </div>
    `
            },
            master_komponen: {
                title: 'Kelola Master Komponen',
                action: '<?= base_url("app/simpan_ref_komponen"); ?>',
                html: `
        <div id="listKomponen">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="small text-muted">Daftar Komponen Saat Ini</span>
                <button type="button" class="btn btn-sm btn-primary rounded-pill" onclick="showFormKomponen()">
                    <i class="bi bi-plus-lg"></i> Tambah Baru
                </button>
            </div>
            <div class="list-group list-group-flush border rounded-3 mb-3" style="max-height: 300px; overflow-y: auto;">
                <?php foreach ($ref_komponen as $k): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <div>
                        <div class="fw-bold small"><?= html_escape($k->nama_komponen); ?></div>
                        <div class="text-muted" style="font-size: 0.7rem;"><?= number_format($k->interval_km, 0, ',', '.'); ?> KM / <?= $k->interval_bulan; ?> Bln</div>
                    </div>
                    <div class="btn-group">
    <button type="button" class="btn btn-sm btn-outline-secondary border-0" 
            onclick='editKomponen(event, <?= str_replace("'", "&apos;", json_encode($k)); ?>)'>
        <i class="bi bi-pencil"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger border-0" 
        onclick="hapusKomponen(event, <?= $k->id; ?>)">
    <i class="bi bi-trash"></i>
</button>
</div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($ref_komponen)) echo '<div class="p-3 text-center text-muted small">Belum ada data</div>'; ?>
            </div>
        </div>

        <div id="formKomponen" class="d-none">
            <input type="hidden" name="id" id="comp_id">
            <div class="mb-2">
                <label class="small fw-bold">Nama Komponen</label>
                <input type="text" name="nama_komponen" id="comp_nama" class="form-control form-control-sm" required>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="small fw-bold">Interval (KM)</label>
                    <input type="number" name="interval_km" id="comp_km" class="form-control form-control-sm" required>
                </div>
                <div class="col-6">
                    <label class="small fw-bold">Interval (Bulan)</label>
                    <input type="number" name="interval_bulan" id="comp_bulan" class="form-control form-control-sm">
                </div>
            </div>
            <div class="mb-3">
                <label class="small fw-bold">Kategori</label>
                <select name="kategori" id="comp_kat" class="form-select form-select-sm">
                    <option value="mesin">Mesin</option>
                    <option value="transmisi">Transmisi</option>
                    <option value="kelistrikan">Kelistrikan</option>
                    <option value="body">Body / Ban</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-light flex-fill" onclick="hideFormKomponen()">Batal</button>
            </div>
        </div>
    `
            },
            edit_motor: {
                title: 'Edit Profil Motor',
                action: '<?= base_url("app/update_motor"); ?>',
                html: `
        <div class="mb-3">
            <label class="small fw-bold">Nama Motor</label>
            <input type="text" name="nama" class="form-control" value="<?= $motor->nama; ?>" required>
        </div>
        <div class="mb-3">
            <label class="small fw-bold">Plat Nomor</label>
            <input type="text" name="plat_nomor" class="form-control" value="<?= $motor->plat_nomor; ?>" required>
        </div>
        <div class="mb-0">
            <label class="small fw-bold">Odometer Awal (KM)</label>
            <input type="number" name="odo_current_km" class="form-control" value="<?= $motor->odo_current_km; ?>" required>
            <small class="text-muted" style="font-size: 0.65rem;">KM saat motor pertama kali didaftarkan di aplikasi.</small>
        </div>
    `
            },

        };

        // Fungsi memicu Modal
        function openModal(type) {
            const config = formTemplates[type];
            document.getElementById('modalTitle').innerText = config.title;
            document.getElementById('formContent').innerHTML = config.html;
            document.getElementById('formDinamis').dataset.action = config.action;

            // Default: Tombol simpan muncul, kecuali untuk master_komponen (list view)
            const btnSimpan = document.getElementById('btnSimpan');
            if (type === 'master_komponen') {
                btnSimpan.classList.add('d-none');
            } else {
                btnSimpan.classList.remove('d-none');
            }

            toggleFab();
            const myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDinamis'));
            myModal.show();
        }

        function showFormKomponen() {
            document.getElementById('listKomponen').classList.add('d-none');
            document.getElementById('formKomponen').classList.remove('d-none');
            document.getElementById('btnSimpan').classList.remove('d-none'); // Munculkan tombol simpan modal
            document.getElementById('comp_id').value = ""; // Reset ID (untuk mode tambah)
            document.getElementById('formDinamis').reset();
        }

        function hideFormKomponen() {
            document.getElementById('listKomponen').classList.remove('d-none');
            document.getElementById('formKomponen').classList.add('d-none');
            document.getElementById('btnSimpan').classList.add('d-none'); // Sembunyikan tombol simpan modal
        }

        function editKomponen(event, data) {

            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Pastikan form input sudah ada di DOM sebelum diisi
            showFormKomponen();

            // Beri sedikit delay agar transisi d-none selesai atau pastikan elemen target ada
            setTimeout(() => {
                document.getElementById('modalTitle').innerText = "Edit Komponen";
                document.getElementById('comp_id').value = data.id || "";
                document.getElementById('comp_nama').value = data.nama_komponen || "";
                document.getElementById('comp_km').value = data.interval_km || 0;
                document.getElementById('comp_bulan').value = data.interval_bulan || 0;
                document.getElementById('comp_kat').value = data.kategori || "mesin";
            }, 50);

            // Trigger masking manual untuk field angka saat edit
            const kmField = document.getElementById('comp_km');
            if (kmField) applyMask(kmField);

        }

        async function hapusKomponen(event, id) {
            // 1. Cegah aksi default agar form tidak submit
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            // 2. Validasi ID (pastikan bukan objek)
            if (typeof id === 'object') {
                console.error("Kesalahan: ID yang diterima adalah objek event. Periksa urutan argumen.");
                return;
            }

            const result = await Swal.fire({
                title: 'Hapus Komponen?',
                text: "Data yang sudah digunakan di rencana perawatan tidak bisa dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (result.isConfirmed) {
                try {
                    // Gunakan template literal dengan benar untuk menyisipkan ID
                    const response = await fetch(`<?= base_url("app/hapus_ref_komponen/"); ?>${id}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const out = await response.json();

                    if (out.ok) {
                        Swal.fire('Terhapus!', out.msg, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', out.msg, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Terjadi kesalahan koneksi atau server.', 'error');
                }
            }
        }

        // Proses AJAX Submit
        // Update AJAX Submit dengan SweetAlert
        document.getElementById('formDinamis').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSimpan');
            const actionUrl = this.dataset.action;

            btn.disabled = true;
            Swal.fire({
                title: 'Menyimpan data...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.msg,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', result.msg, 'error');
                    btn.disabled = false;
                }
            } catch (error) {
                Swal.fire('Error', 'Terjadi kesalahan sistem atau koneksi.', 'error');
                btn.disabled = false;
            }
        });

        async function getCurrentLocation(event) {
            const btn = event.currentTarget;
            const container = btn.closest('.modal-body') || btn.closest('form');
            const inputLabel = container.querySelector('.loc-label');
            const inputLat = container.querySelector('.loc-lat');
            const inputLng = container.querySelector('.loc-lng');

            btn.disabled = true;
            const originalContent = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;

            // Toast configuration untuk notifikasi ringan
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            if (!navigator.geolocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Geolocation tidak didukung oleh browser Anda.',
                });
                btn.disabled = false;
                btn.innerHTML = originalContent;
                return;
            }

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                inputLat.value = lat;
                inputLng.value = lng;

                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 4000);

                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
                        signal: controller.signal,
                        headers: {
                            'User-Agent': 'MotorTrackPro_App_v1'
                        }
                    });

                    if (response.status === 403 || response.status === 429) throw new Error("API_LIMIT");

                    const data = await response.json();
                    const address = data.address.road || data.address.amenity || data.address.suburb || data.address.city || "Lokasi Terdeteksi";

                    inputLabel.value = address;
                    btn.innerHTML = `<i class="bi bi-check-lg text-success"></i>`;

                    Toast.fire({
                        icon: 'success',
                        title: 'Lokasi berhasil didapatkan'
                    });

                    clearTimeout(timeoutId);
                } catch (err) {
                    inputLabel.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    btn.innerHTML = `<i class="bi bi-geo-alt text-warning"></i>`;

                    Toast.fire({
                        icon: 'warning',
                        title: 'Menggunakan koordinat (API Limit)'
                    });
                } finally {
                    btn.disabled = false;
                }
            }, (error) => {
                let title = "Gagal Akses GPS";
                let msg = "Pastikan GPS aktif dan izin lokasi diberikan.";

                if (error.code === 1) {
                    title = "Izin Ditolak";
                    msg = "Aplikasi butuh izin lokasi untuk fitur ini.";
                } else if (error.code === 3) {
                    title = "Waktu Habis";
                    msg = "Sinyal GPS terlalu lemah, coba lagi di tempat terbuka.";
                }

                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: msg,
                    confirmButtonColor: '#0d6efd'
                });

                btn.disabled = false;
                btn.innerHTML = originalContent;
            }, {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            });
        }

        function applyMask(el) {
            // 1. Ambil angka saja
            let value = el.value.replace(/\D/g, "");

            // 2. Format dengan titik sebagai ribuan
            let formatted = new Intl.NumberFormat('id-ID').format(value);

            // 3. Tampilkan yang sudah diformat ke input text
            el.value = (value === "") ? "" : formatted;

            // 4. Masukkan angka asli (tanpa titik) ke hidden input tepat setelah element ini
            // Hidden input harus diletakkan persis setelah input masking
            if (el.nextElementSibling && el.nextElementSibling.type === 'hidden') {
                el.nextElementSibling.value = value;
            }
        }
    </script>

    <script>
        function formatTanggalIndo(dateString) {
            const opsi = {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            };
            return new Date(dateString).toLocaleDateString('id-ID', opsi);
        }

        async function loadRiwayat() {
            const container = document.getElementById('riwayatContainer');
            const totalBox = document.getElementById('totalRiwayatContainer');
            const textTotal = document.getElementById('textTotalBiaya');
            const textItem = document.getElementById('textTotalItem');

            const thn = document.getElementById('histFilterYear').value;
            const bln = document.getElementById('histFilterMonth').value;
            const tipe = document.getElementById('histFilterType').value;

            container.innerHTML = '<div class="text-center p-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
            totalBox.classList.add('d-none');

            try {
                const res = await fetch(`<?= base_url("app/get_riwayat_all"); ?>?motor_id=${motorId}&tahun=${thn}&bulan=${bln}&tipe=${tipe}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await res.json();
                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center p-5 text-muted small">Tidak ada catatan pada periode ini.</div>';
                    return;
                }

                // --- ANALITIK LOGIC ---
                const chronologicalData = [...data].sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
                let lastData = {
                    bensin: null,
                    oli: null,
                    servis: null
                };
                let insights = {};

                chronologicalData.forEach(item => {
                    const prev = lastData[item.tipe];
                    if (prev) {
                        const selisihKm = item.odo_display_km - prev.odo_display_km;
                        const selisihHari = Math.round((new Date(item.tanggal) - new Date(prev.tanggal)) / (1000 * 60 * 60 * 24));
                        const costPerKm = selisihKm > 0 ? (item.biaya / selisihKm) : 0;
                        insights[`${item.tipe}_${item.id}`] = {
                            km: selisihKm,
                            hari: selisihHari,
                            perKm: costPerKm
                        };
                    }
                    lastData[item.tipe] = item;
                });

                let totalBiaya = 0;
                data.forEach(item => totalBiaya += parseInt(item.biaya || 0));
                textTotal.innerText = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(totalBiaya);
                textItem.innerText = `${data.length} Transaksi`;
                totalBox.classList.remove('d-none');

                let html = '';
                data.forEach(item => {
                    let iconClass = 'bi-fuel-pump';
                    let accentColor = item.tipe === 'bensin' ? '#0d6efd' : (item.tipe === 'oli' ? '#ffc107' : '#198754');
                    let bgIcon = item.tipe === 'bensin' ? 'bg-primary-subtle text-primary' : (item.tipe === 'oli' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success');

                    const biayaFull = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(item.biaya);
                    const info = insights[`${item.tipe}_${item.id}`];

                    // Kondisi Maps & Lokasi
                    const hasLocation = (item.latitude && item.longitude) || item.lokasi_label;
                    const mapsUrl = (item.latitude && item.longitude) ?
                        `https://www.google.com/maps/search/?api=1&query=${item.latitude},${item.longitude}` :
                        `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(item.lokasi_label)}`;

                    html += `
            <div class="card border-0 shadow-sm mb-3 mx-2" style="border-radius: 18px; border-left: 5px solid ${accentColor} !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="fw-bold text-muted"><i class="bi bi-calendar3 me-1"></i> ${formatTanggalIndo(item.tanggal)}</small>
                        ${hasLocation ? `
                            <a href="${mapsUrl}" target="_blank" class="text-decoration-none" style="font-size: 0.7rem;">
                                <span class="badge rounded-pill bg-light text-primary border border-primary-subtle">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Maps
                                </span>
                            </a>
                        ` : ''}
                    </div>

                    <div class="row align-items-center g-0">
                        <div class="col-5 border-end pe-2">
                            <div class="d-flex align-items-start gap-2">
                                <div class="rounded-3 ${bgIcon} d-flex align-items-center justify-content-center shadow-sm" style="min-width: 38px; height: 38px;">
                                    <i class="bi ${iconClass} fs-5"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-capitalize small" style="line-height: 1.2;">${item.tipe}</div>
                                    <div class="text-primary fw-semibold" style="font-size: 0.7rem;">${item.detail || '-'}</div>
                                    ${item.lokasi_label ? `
                                        <div class="text-muted text-truncate mt-1" style="font-size: 0.65rem;">
                                            <i class="bi bi-shop me-1"></i>${item.lokasi_label}
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>

                        <div class="col-3 border-end px-2 text-center">
                            <div class="mb-2">
                                <div class="fw-bold text-dark" style="font-size: 0.75rem;">${info ? info.km : '0'} KM</div>
                                <div class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Jarak</div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.75rem;">${info ? info.hari : '0'} Hr</div>
                                <div class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Usia</div>
                            </div>
                        </div>

                        <div class="col-4 ps-2 text-end">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">${biayaFull}</div>
                            <div class="text-muted mb-1" style="font-size: 0.6rem;">Biaya</div>
                            <div class="badge rounded-pill bg-dark text-white fw-normal" style="font-size: 0.6rem; letter-spacing: 0.3px;">
                                Rp ${info ? Math.round(info.perKm) : 0} / KM
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted text-truncate pe-2" style="font-size: 0.7rem;">
                            <i class="bi bi-speedometer2 me-1"></i>Odo: ${new Intl.NumberFormat('id-ID').format(item.odo_display_km)} KM
                        </small>
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusRiwayat(event, '${item.tipe}', ${item.id})" style="width: 28px; height: 28px; padding: 0;">
                            <i class="bi bi-trash3" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>
                </div>
            </div>`;
                });
                container.innerHTML = html;
            } catch (e) {
                console.error(e);
                container.innerHTML = '<div class="alert alert-danger mx-3 shadow-sm border-0">Gagal memuat data.</div>';
            }
        }

        async function loadMaintenance() {
            const container = document.getElementById('maintenanceContainer');

            // Tampilkan loading spinner
            container.innerHTML = '<div class="text-center p-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

            try {
                const res = await fetch(`<?= base_url("app/get_maintenance_status"); ?>?motor_id=${motorId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();

                if (data.plans.length === 0) {
                    container.innerHTML = `
            <div class="text-center py-5 border rounded-4 bg-white">
                <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                <p class="text-muted small mt-2">Belum ada komponen yang dipantau.<br>Gunakan tombol + Update Part.</p>
            </div>`;
                    return;
                }

                let html = '';
                data.plans.forEach(p => {
                    // Logika teks sisa hari
                    let sisaHariLabel = "";
                    if (p.sisa_hari <= 0) {
                        sisaHariLabel = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Segera Ganti!</span>`;
                    } else {
                        sisaHariLabel = `<span class="text-muted"><i class="bi bi-calendar-event"></i> ±${p.sisa_hari} hari lagi</span>`;
                    }

                    html += `
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <span class="fw-bold text-dark">${p.nama_komponen}</span>
                            <span class="badge bg-light text-muted border ms-1" style="font-size: 0.6rem; text-transform: uppercase;">${p.kategori}</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-${p.status_color}">${p.persen}%</span>
                        </div>
                    </div>
                    
                    <div class="mb-2" style="font-size: 0.75rem;">
                        ${sisaHariLabel}
                    </div>

                    <div class="progress" style="height: 8px; border-radius: 4px; background-color: #f0f0f0;">
                        <div class="progress-bar bg-${p.status_color}" role="progressbar" style="width: ${p.persen}%"></div>
                    </div>

                    <div class="d-flex justify-content-between mt-2" style="font-size: 0.7rem;">
                        <span class="text-muted italic">Terakhir: ${new Intl.NumberFormat('id-ID').format(p.last_service_odo)} KM</span>
                        <span class="fw-bold ${p.sisa_km < 0 ? 'text-danger' : 'text-dark'}">
                            ${p.sisa_km < 0 ? 'Lewat ' + Math.abs(p.sisa_km) : 'Sisa ' + new Intl.NumberFormat('id-ID').format(p.sisa_km)} KM
                        </span>
                    </div>
                </div>
            </div>`;
                });

                container.innerHTML = html;
            } catch (e) {
                console.error(e);
                container.innerHTML = '<div class="alert alert-danger">Gagal memuat jadwal. Silakan coba lagi.</div>';
            }
        }

        function showPage(pageId, btn) {
            // 1. Sembunyikan semua halaman
            document.querySelectorAll('.page-view').forEach(p => {
                p.classList.add('d-none');
            });

            // 2. Tampilkan halaman yang dituju
            const targetPage = document.getElementById('page-' + pageId);
            if (targetPage) {
                targetPage.classList.remove('d-none');
            }

            // 3. Update status active pada tombol navigasi
            document.querySelectorAll('.nav-link-custom').forEach(b => {
                b.classList.remove('active');
            });
            if (btn) btn.classList.add('active'); // Tambahkan pengecekan jika btn ada

            // 4. Tutup FAB Menu jika sedang terbuka
            const menu = document.getElementById('fabMenu');
            const icon = document.getElementById('fabIcon');
            if (menu && menu.classList.contains('show')) {
                menu.classList.remove('show');
                if (icon) icon.classList.replace('bi-x-lg', 'bi-plus-lg');
            }

            // 5. Trigger pemuatan data otomatis berdasarkan halaman
            if (pageId === 'history') {
                loadRiwayat();
            } else if (pageId === 'maintenance') {
                loadMaintenance(); // Memanggil fungsi jadwal yang kita buat sebelumnya
            }
        }

        async function hapusRiwayat(event, tipe, id) {
            // Mencegah bubbling agar tidak memicu aksi lain pada elemen pembungkus
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const confirm = await Swal.fire({
                title: 'Hapus Catatan?',
                text: "Data pengeluaran ini akan dihapus permanen dari sistem.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (confirm.isConfirmed) {
                // Mapping endpoint berdasarkan tipe riwayat
                const endpoints = {
                    'bensin': 'delete_minyak',
                    'oli': 'delete_oli',
                    'servis': 'delete_service'
                };

                const url = endpoints[tipe];

                // Tampilkan loading saat proses hapus
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const res = await fetch(`<?= base_url("app/"); ?>${url}`, {
                        method: 'POST',
                        body: new URLSearchParams({
                            id: id,
                            motor_id: motorId // Pastikan const motorId sudah dideklarasikan di awal script
                        }),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const out = await res.json();

                    if (out.ok) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: out.msg || 'Catatan telah berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Refresh daftar riwayat tanpa reload halaman
                        loadRiwayat();

                        // Jika Anda ingin mengupdate dashboard juga (karena KM mungkin berubah)
                        // location.reload(); // Gunakan ini jika data dashboard harus sinkron seketika
                    } else {
                        Swal.fire('Gagal', out.msg || 'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                } catch (e) {
                    console.error("Error Delete:", e);
                    Swal.fire('Error', 'Terjadi kesalahan koneksi ke server.', 'error');
                }
            }
        }
    </script>

    <script>
        // Letakkan paling atas!
        // const motorId = <?= isset($motor->id) ? $motor->id : 0; ?>;
        // const motorOdoSekarang = <?= isset($motor->odo_current_km) ? $motor->odo_current_km : 0; ?>;

        let myChart = null; // Variable global untuk menyimpan instance chart

        async function loadDashboardData() {
            const bln = document.getElementById('dashFilterMonth').value;
            const thn = document.getElementById('dashFilterYear').value;

            try {
                const res = await fetch(`<?= base_url("app/get_dashboard_stats"); ?>?motor_id=${motorId}&bulan=${bln}&tahun=${thn}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();

                // 1. Update Angka Statistik
                document.getElementById('dashTotalBiaya').innerText = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(data.total_biaya);
                document.getElementById('dashEfisiensi').innerHTML = `${data.efisiensi} <small>km/L</small>`;
                document.getElementById('dashTotalOdo').innerText = new Intl.NumberFormat('id-ID').format(data.odo_total);

                // 2. Render Grafik
                renderChart(data.chart_labels, data.chart_values);

                // 3. Render Kesehatan (Hanya ambil 2 yang terburuk/terkecil persennya)
                renderHealthDashboard(data.health);

                // 4. Render Riwayat Singkat
                renderRecentHistory(data.recent);

            } catch (e) {
                console.error("Dashboard Load Error", e);
            }
        }

        function renderChart(labels, values) {
            const ctx = document.getElementById('expenseChart').getContext('2d');

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'line', // Gunakan 'bar' jika Anda lebih suka tampilan batang untuk tahunan
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pengeluaran',
                        data: values,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.3 // Membuat garis lebih smooth
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            display: false
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 9
                                }
                            }
                        }
                    }
                }
            });
        }

        function renderHealthDashboard(health) {
            const container = document.getElementById('dashHealthGrid');

            if (!health || health.length === 0) {
                container.innerHTML = `
            <div class="health-card w-100 text-center py-3">
                <small class="text-muted">Belum ada komponen dipantau</small>
            </div>`;
                return;
            }

            let html = '';
            // Kita tampilkan 2 komponen paling kritis di dashboard
            health.forEach(p => {
                html += `
        <div class="health-card" onclick="showPage('maintenance')" style="cursor: pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small fw-bold text-truncate" style="max-width: 80px;">${p.nama_komponen}</span>
                <i class="bi bi-shield-check text-${p.status_color}"></i>
            </div>
            <div class="fw-bold mt-1 text-${p.status_color}">${p.persen}%</div>
            <div class="progress-tiny">
                <div class="progress-bar bg-${p.status_color}" style="width: ${p.persen}%"></div>
            </div>
            <small class="text-muted mt-1 d-block" style="font-size: 0.6rem;">
                ${p.sisa_km < 0 ? 'Wajib Ganti' : 'Sisa ' + new Intl.NumberFormat('id-ID').format(p.sisa_km) + ' km'}
            </small>
        </div>`;
            });

            container.innerHTML = html;
        }

        function renderRecentHistory(recent) {
            const container = document.getElementById('dashRecentHistory');
            let html = '';
            recent.forEach(item => {
                let icon = item.tipe === 'bensin' ? 'bi-fuel-pump text-primary' : (item.tipe === 'oli' ? 'bi-droplet text-warning' : 'bi-tools text-success');
                html += `
        <div class="history-item">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-light p-2"><i class="bi ${icon}"></i></div>
                <div>
                    <div class="fw-bold small text-capitalize">${item.tipe}</div>
                    <small class="text-muted" style="font-size: 0.7rem;">${formatTanggalIndo(item.tanggal)}</small>
                </div>
            </div>
            <div class="text-end fw-bold small">Rp ${new Intl.NumberFormat('id-ID').format(item.biaya)}</div>
        </div>`;
            });
            container.innerHTML = html || '<div class="text-center p-3 text-muted small">Belum ada riwayat</div>';
        }
    </script>


</body>

</html>