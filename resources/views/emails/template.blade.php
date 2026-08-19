<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset */
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; }
        table { border-collapse: collapse; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body, table, td { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }

        /* Layout */
        .email-wrapper { width: 100%; background: #f4f4f4; padding: 20px 0; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
        .email-header { padding: 24px 32px; border-bottom: 1px solid #e5e7eb; }
        .email-body { padding: 32px; color: #374151; line-height: 1.6; }
        .email-footer { padding: 16px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
        .email-button { display: inline-block; padding: 12px 24px; background: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 500; }
        @media only screen and (max-width: 640px) {
            .email-container { width: 100% !important; border-radius: 0; }
            .email-header, .email-body, .email-footer { padding: 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background: #f4f4f4;">
    <table class="email-wrapper" role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table class="email-container" role="presentation" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td class="email-header">
                            <h1 style="margin: 0; font-size: 20px; color: #111827;">{{ config_value('site.name', 'VertexCMS') }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-body">
                            {!! $content !!}
                        </td>
                    </tr>
                    <tr>
                        <td class="email-footer">
                            <p style="margin: 0;">© {{ date('Y') }} {{ config_value('site.name', 'VertexCMS') }}. Все права защищены.</p>
                            @if(config_value('mail.reply_to_address'))
                                <p style="margin: 8px 0 0;">Ответы: <a href="mailto:{{ config_value('mail.reply_to_address') }}" style="color:#9ca3af;">{{ config_value('mail.reply_to_address') }}</a></p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
