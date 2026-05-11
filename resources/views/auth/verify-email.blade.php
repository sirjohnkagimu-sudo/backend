<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">
                <div class="card-header text-center">
                    <h3>Email Verification</h3>
                </div>

                <div class="card-body">

                    <div class="mb-4 text-muted">
                        Thanks for signing up! Before getting started,
                        please verify your email address by clicking the
                        link we just emailed to you.

                        If you didn't receive the email,
                        we will gladly send you another.
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success">
                            A new verification link has been sent to the
                            email address you provided during registration.
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">

                        {{-- Resend Verification --}}
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf

                            <button type="submit" class="btn btn-primary">
                                Resend Verification Email
                            </button>
                        </form>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit" class="btn btn-link text-danger">
                                Log Out
                            </button>
                        </form>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>