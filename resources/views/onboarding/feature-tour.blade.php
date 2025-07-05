@extends('layouts.onboarding')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-brandprimary text-white">
                    <h2 class="mb-0">Feature Tour</h2>
                </div>
                <div class="card-body">
                    <div class="onboarding-steps mb-4">
                        <div class="step completed">
                            <span class="step-number">1</span>
                            <span class="step-text">Welcome</span>
                        </div>
                        <div class="step completed">
                            <span class="step-number">2</span>
                            <span class="step-text">Your Account</span>
                        </div>
                        <div class="step completed">
                            <span class="step-number">3</span>
                            <span class="step-text">Business Info</span>
                        </div>
                        <div class="step active">
                            <span class="step-number">4</span>
                            <span class="step-text">Feature Tour</span>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <h3>Welcome to Faxtina!</h3>
                        <p class="lead">Here's a quick overview of key features to help you get started</p>
                    </div>

                    <div id="featureCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                            <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="feature-slide">
                                    <div class="feature-icon">
                                        <i class="fas fa-calendar-alt fa-3x"></i>
                                    </div>
                                    <h4>Appointment Management</h4>
                                    <p>Easily schedule, manage, and track all your appointments in one place. Send automated reminders to reduce no-shows.</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="feature-slide">
                                    <div class="feature-icon">
                                        <i class="fas fa-cash-register fa-3x"></i>
                                    </div>
                                    <h4>Point of Sale</h4>
                                    <p>Process payments quickly and efficiently with our integrated POS system. Track sales, manage inventory, and generate reports.</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="feature-slide">
                                    <div class="feature-icon">
                                        <i class="fas fa-envelope fa-3x"></i>
                                    </div>
                                    <h4>Email Marketing</h4>
                                    <p>Create and send beautiful email campaigns to your clients. Track opens, clicks, and conversions to optimize your marketing efforts.</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="feature-slide">
                                    <div class="feature-icon">
                                        <i class="fas fa-chart-line fa-3x"></i>
                                    </div>
                                    <h4>Reports & Analytics</h4>
                                    <p>Gain valuable insights into your business performance with detailed reports and analytics. Make data-driven decisions to grow your business.</p>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#featureCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#featureCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <form method="POST" action="{{ route('onboarding.complete') }}">
                            @csrf
                            <button type="submit" class="btn btn-brand-primary btn-lg">
                                {{ __('Go to Dashboard') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Onboarding styles now loaded from onboarding.css -->

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myCarousel = document.getElementById('featureCarousel');
        var carousel = new bootstrap.Carousel(myCarousel, {
            interval: 5000,
            wrap: true
        });
    });
</script>
@endsection
@endsection
