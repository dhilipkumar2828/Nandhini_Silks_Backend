<!DOCTYPE html>
<html>
<head>
    <title>Inquiry Received</title>
</head>
<body>
    <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="{{ $message->embed(public_path('images/nandhini-logo.png')) }}" alt="Nandhini Silks" style="height: 60px; width: auto;">
        </div>
        
        <h1 style="color: #333; font-size: 22px; margin-bottom: 20px;">Hello {{ $inquiry->name }},</h1>
        <p style="color: #555; line-height: 1.6;">Thank you for reaching out to us. We have received your inquiry and will get back to you as soon as possible.</p>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #777; font-size: 12px; text-transform: uppercase;">Your Message:</p>
            <p style="margin: 10px 0 0; color: #333; font-style: italic;">"{{ $inquiry->message }}"</p>
        </div>
        
        <p style="color: #777; font-size: 14px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            Best Regards,<br>
            <strong>Nandhini Silks Team</strong>
        </p>
    </div>
</body>
</html>
