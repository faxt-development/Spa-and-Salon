@extends('layouts.onboarding')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Welcome to Faxtina!</h2>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Faxtina Logo" class="img-fluid mb-4" style="max-width: 200px;">
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
                        <p>In the next few steps, we'll help you:</p>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item">Create your admin account</li>
                            <li class="list-group-item">Set up your business information</li>
                            <li class="list-group-item">Learn about key features</li>
                        </ul>

                        <a href="{{ route('onboarding.user-form') }}" class="btn btn-primary btn-lg">Let's Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .onboarding-steps {
        display: flex;
        justify-content: space-between;
        margin: 30px 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        width: 25%;
    }

    .step:not(:last-child):after {
        content: '';
        position: absolute;
        top: 15px;
        right: -50%;
        width: 100%;
        height: 2px;
        background-color: #e0e0e0;
        z-index: 0;
    }

    .step.active .step-number {
        background-color: #4299e1;
        color: white;
    }

    .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        font-weight: bold;
        z-index: 1;
    }

    .step-text {
        font-size: 0.8rem;
        color: #718096;
    }

    .step.active .step-text {
        color: #4299e1;
        font-weight: bold;
    }
</style>
@endsection
