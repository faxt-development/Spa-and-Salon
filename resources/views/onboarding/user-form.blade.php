@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Create Your Admin Account</h2>
                </div>
                <div class="card-body">
                    <div class="onboarding-steps mb-4">
                        <div class="step completed">
                            <span class="step-number">1</span>
                            <span class="step-text">Welcome</span>
                        </div>
                        <div class="step active">
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

                    <p class="lead text-center mb-4">Create your admin account to manage your spa or salon</p>

                    <form method="POST" action="{{ route('onboarding.process-user') }}">
                        @csrf

                        <div class="form-group row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Full Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Continue') }}
                                </button>
                            </div>
                        </div>
                    </form>
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
    
    .step.completed .step-number {
        background-color: #48bb78;
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
    
    .step.completed .step-text {
        color: #48bb78;
        font-weight: bold;
    }
</style>
@endsection
