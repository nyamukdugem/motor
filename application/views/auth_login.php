<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login MotorKu — OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow-sm" style="max-width: 380px; width: 100%;">
            <div class="card-body p-4">
                <h5 class="card-title mb-3 text-center">Login MotorKu</h5>
                <p class="text-muted small text-center mb-4">
                    Masukkan kode 6 digit dari aplikasi <strong>Google Authenticator</strong>.
                </p>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger py-2">
                        <?= $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success py-2">
                        <?= $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label for="otp" class="form-label">Kode OTP</label>
                        <input type="text"
                            class="form-control text-center fs-4"
                            id="otp" name="otp"
                            maxlength="6"
                            placeholder="••••••"
                            autocomplete="one-time-code"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Login
                    </button>
                </form>

                <p class="text-center mt-3 mb-0 small text-muted">
                    Kode berubah setiap 30 detik.
                </p>
            </div>
        </div>
    </div>

</body>

</html>