@extends('emails.layout')

@section('title', "Session terminée : « {$round->word} »")
@section('preheader', $stories->count() . ' ' . ($stories->count() > 1 ? 'textes sont' : 'texte est') . " à lire sur le mot « {$round->word} ».")
@section('eyebrow', 'Session terminée')
@section('heading', 'Les textes sont ouverts')

@section('content')
    <p style="margin:0;">
        Bonjour {{ $plume->first_name }}, la session proposée par <strong>{{ $master?->first_name }} {{ $master?->last_name }}</strong> est close.
    </p>

    @include('emails.partials.word', ['word' => $round->word])

    @if ($stories->isEmpty())
        <p style="margin:0 0 10px;">Aucun texte n'a été rendu sur cette session.</p>
    @else
        <p style="margin:0 0 10px;">
            {{ $stories->count() }} {{ $stories->count() > 1 ? 'textes rendus, désormais à lire' : 'texte rendu, désormais à lire' }} :
        </p>
    @endif

    @if ($recap->isNotEmpty())
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            @foreach ($recap as $entry)
                <tr>
                    <td style="padding:10px 0;border-top:1px solid #EDEDF2;font-family:Lato,Arial,sans-serif;font-size:15px;color:#3A3641;">
                        @if ($entry['joker'])
                            <span style="color:#89868D;"><s>{{ $entry['plume']->first_name }} {{ $entry['plume']->last_name }}</s> (joker)</span>
                        @else
                            <strong style="color:#363062;">{{ $entry['plume']?->first_name }} {{ $entry['plume']?->last_name }}</strong>
                            @if ($entry['title'])
                                <span style="color:#788B9A;"> — {{ $entry['title'] }}</span>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($playedJoker)
        <p style="margin:16px 0 0;color:#788B9A;font-size:14px;">
            Vous aviez posé un joker : rien à rendre, mais tout à lire.
        </p>
    @endif

    @include('emails.partials.button', ['url' => $url, 'label' => 'Lire les textes'])

    @if ($nextSelector)
        <p style="margin:18px 0 0;font-family:Caveat,cursive;font-size:21px;color:#9255FD;">
            @if ($nextSelector->is($plume))
                C'est à vous de choisir le prochain mot !
            @else
                Prochain mot choisi par {{ $nextSelector->first_name }}.
            @endif
        </p>
    @endif
@endsection
