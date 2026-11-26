
@extends('layouts.app')

@section('title', 'Email Verification')

@section('content')
<div class="container min-vh-100 d-flex justify-content-center align-items-center">

    <div class="card shadow border-0 text-center" style="max-width: 450px; width: 100%;">
        <div class="card-body p-5">

            @if($success)
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success"
                       style="font-size: 65px;"></i>
                </div>

                <h3 class="text-success mb-3">
                    Email Verified Successfully!
                </h3>

                <p class="text-muted">{{ $message }}</p>

                <p>
                    Your email address has been verified.
                    You can now log in to your LMS account.
                </p>

                <a href="/login" class="btn btn-primary w-100 mt-3">
                    Go to Login
                </a>
            @else
                <div class="mb-3">
                    <i class="bi bi-x-circle-fill text-danger"
                       style="font-size: 65px;"></i>
                </div>

                <h3 class="text-danger mb-3">
                    Verification Failed
                </h3>

                <p class="text-muted">{{ $message }}</p>

                <a href="/login" class="btn btn-primary w-100 mt-3">
                    Go to Login
                </a>
            @endif

        </div>
    </div>

</div>
@endsection
