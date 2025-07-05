@extends('layouts.onboarding')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-brandprimary text-white">
                    <h2 class="mb-0">Welcome to Faxtina!</h2>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/faxtina-logo.png') }}" alt="Faxtina Logo" class="img-fluid mb-4" style="max-width: 200px;">
                        <h3>Thank you for choosing Faxtina for your spa and salon management needs!</h3>
                        <p class="lead">Let's get your account set up so you can start using all the features right away.</p>
                    </div>

                    <div class="onboarding-steps mb-4">
                        <div class="step active">
                            <span class="step-number">1</span>
                            <span class="step-text">Welcome</span>
                        </div>
                        <div class="step">
                            <span class="step-number">2</span>
                            <span class="step-text">Your Account</span>
                        </div>
                        <div class="step">
                            <span class="step-number">3</span>
                            <span class="step-text">Business Info</span>
                        </div>
                        <div class="step">
                            <span class="step-number">4</span>
                            <span class="step-text">Feature Tour</span>
                        </div>
                    </div>

                    <div class="text-center">
                        @if(isset($user) && $user->email)
                            <div class="alert alert-info mb-4">
                                Welcome back, {{ $user->email }}! Let's continue setting up your account.
                            </div>
                        @endif
                        
                        <p>In the next few steps, we'll help you:</p>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item">{{ isset($user) && $user->email ? 'Complete' : 'Create' }} your admin account</li>
                            <li class="list-group-item">Set up your business information</li>
                            <li class="list-group-item">Learn about key features</li>
                        </ul>

                        <a href="{{ route('onboarding.user-form') }}" class="btn btn-brand-primary btn-lg">
                            {{ isset($user) && $user->email ? 'Continue Setup' : "Let's Get Started" }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Onboarding styles now loaded from onboarding.css -->
@endsection
