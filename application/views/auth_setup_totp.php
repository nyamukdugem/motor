<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Setup Google Authenticator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow-sm" style="max-width: 480px; width: 100%;">
            <div class="card-body p-4">
                <h5 class="card-title mb-3 text-center">Aktivasi Google Authenticator</h5>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger py-2">
                        <?= $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <ol class="small mb-3">
                    <li>Install aplikasi <strong>Google Authenticator</strong> di HP (Android/iOS).</li>
                    <li>Buka aplikasinya, pilih <em>Scan QR code</em>.</li>
                    <li>Scan QR code di bawah ini.</li>
                    <li>Masukkan kode 6 digit yang muncul untuk konfirmasi.</li>
                </ol>

                <div class="text-center mb-3">
                    <img src="<?= $qrCodeUrl; ?>" alt="QR Code" class="img-fluid border rounded p-2 bg-white">
                </div>

                <p class="text-center text-muted mb-1">
                    Atau masukkan manual secret key:
                </p>
                <p class="text-center fw-semibold">
                    <code><?= htmlspecialchars($secret); ?></code>
                </p>

                <form method="post" class="mt-3">
                    <div class="mb-3">
                        <label for="otp" class="form-label">Kode OTP dari Google Authenticator</label>
                        <input type="text" name="otp" id="otp" class="form-control text-center fs-5"
                            placeholder="123456" maxlength="6" autocomplete="one-time-code" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Konfirmasi &amp; Aktifkan
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>