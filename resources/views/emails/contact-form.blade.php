<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-bottom: 1px solid #e9ecef;">
        <h1 style="margin: 0; color: #2d3748;">{{ config('app.name') }}</h1>
    </div>

    <div style="padding: 20px 0;">
        <h2 style="color: #2d3748; margin-top: 0;">New Contact Form Submission</h2>
        
        <p><strong>Name:</strong> {{ $formData['name'] }}</p>
        <p><strong>Email:</strong> <a href="mailto:{{ $formData['email'] }}" style="color: #4299e1; text-decoration: none;">{{ $formData['email'] }}</a></p>
        <p><strong>Subject:</strong> {{ $formData['subject'] }}</p>
        
        <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #4299e1;">
            <p style="margin: 0; white-space: pre-line;">{{ $formData['message'] }}</p>
        </div>
        
        <p>This message was sent from the contact form on {{ config('app.name') }}.</p>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; font-size: 0.9em; color: #718096; text-align: center;">
        <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
