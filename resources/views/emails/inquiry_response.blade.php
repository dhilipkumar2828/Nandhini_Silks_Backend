<!DOCTYPE html>
<html>
<head>
    <title>Response to Inquiry</title>
</head>
<body>
    <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="{{ asset('images/nandhini-logo.png') }}" alt="Nandhini Silks" style="height: 60px; width: auto; background: white; padding: 5px; border-radius: 8px;">
        </div>
        
        <h1 style="color: #333; font-size: 22px; margin-bottom: 20px;">Hello {{ $inquiry->name }},</h1>
        <p style="color: #555; line-height: 1.6;">Thank you for reaching out to us. We have reviewed your inquiry and here is our response:</p>
        
        @if($inquiry->admin_note)
        <div style="background-color: #f0f7ff; padding: 15px; border-left: 4px solid #007bff; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #007bff; font-size: 12px; text-transform: uppercase;">Our Response:</p>
            <p style="margin: 10px 0 0; color: #333; line-height: 1.5;">{!! nl2br(e($inquiry->admin_note)) !!}</p>
        </div>
        @endif

        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #777; font-size: 12px; text-transform: uppercase;">Your Message:</p>
            <p style="margin: 10px 0 0; color: #333; font-style: italic;">"{{ $inquiry->message }}"</p>
        </div>
        
        <p style="color: #777; font-size: 14px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            Best Regards,<br>
            <strong>Nandhini Silks Team</strong><br>
            <span style="font-size: 12px;">{{ config('app.name') }}</span>
        </p>
    </div>
</body>
</html>
