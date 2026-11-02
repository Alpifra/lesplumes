{{--
    The frame of every email of the circle, in the colours and fonts of the
    site. Email clients only trust tables and inline styles, so that is all
    this uses; the web fonts degrade to Georgia / Arial where they are not
    loaded.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=DM+Sans:wght@500;700&family=Caveat:wght@600&family=Cormorant+Garamond:ital,wght@0,700;1,600&display=swap" rel="stylesheet">
    <style>
        @media (max-width: 620px) {
            .mail-card { padding: 28px 22px !important; }
            .mail-title { font-size: 26px !important; }
            .mail-word { font-size: 36px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#F4F5F9;">
    {{-- Preview line shown next to the subject in the inbox. --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">@yield('preheader')</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F9;">
        <tr>
            <td align="center" style="padding:36px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
                    {{-- Wordmark --}}
                    <tr>
                        <td align="center" style="padding-bottom:22px;">
                            <a href="{{ config('app.frontend_url') }}" style="text-decoration:none;">
                                <img src="{{ isset($message) ? $message->embed(resource_path('images/email-logo.png')) : '' }}" width="72" height="72" alt="" style="display:block;margin:0 auto 6px;border:0;">
                                <span style="font-family:'Cormorant Garamond',Georgia,serif;font-weight:700;font-size:30px;color:#363062;letter-spacing:-0.01em;">Les Plumes</span><br>
                                <span style="font-family:Caveat,cursive;font-size:19px;color:#9255FD;">cercle d'écriture</span>
                            </a>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td class="mail-card" style="background:#FFFFFF;border:1px solid #E6E6EC;border-radius:16px;padding:40px 44px;">
                            <p style="margin:0 0 6px;font-family:'DM Sans',Lato,Arial,sans-serif;font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#788B9A;">@yield('eyebrow')</p>
                            <h1 class="mail-title" style="margin:0 0 22px;font-family:'Cormorant Garamond',Georgia,serif;font-weight:700;font-size:32px;line-height:1.15;color:#363062;">@yield('heading')</h1>

                            <div style="font-family:Lato,Arial,sans-serif;font-size:16px;line-height:1.6;color:#3A3641;">
                                @yield('content')
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:26px 12px 0;">
                            <p style="margin:0 0 8px;font-family:Caveat,cursive;font-size:22px;color:#363062;">À vos plumes !</p>
                            <p style="margin:0;font-family:Lato,Arial,sans-serif;font-size:12px;line-height:1.5;color:#89868D;">
                                @hasSection('footer')
                                    @yield('footer')
                                @else
                                    Vous recevez ce mail parce que vous faites partie du cercle des plumes.
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
