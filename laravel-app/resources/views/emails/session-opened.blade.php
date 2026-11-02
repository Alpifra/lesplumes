@extends('emails.layout')

@section('title', "Nouvelle session : « {$round->word} »")
@section('preheader', "{$master?->first_name} a ouvert une session sur le mot « {$round->word} ».")
@section('eyebrow', 'Nouvelle session')
@section('heading', "À vos plumes, {$plume->first_name} !")

@section('content')
    <p style="margin:0;">
        <strong>{{ $master?->first_name }} {{ $master?->last_name }}</strong> vient d'ouvrir une session d'écriture. Le mot sélectionné :
    </p>

    @include('emails.partials.word', ['word' => $round->word])

    <p style="margin:0 0 12px;">
        @if ($round->end_at)
            Vous avez jusqu'au <strong>{{ $round->end_at->locale('fr')->translatedFormat('l j F \à H\hi') }}</strong> pour rendre votre texte.
        @else
            Rendez votre texte quand l'inspiration vient : la session se clôt dès que tout le cercle a rendu le sien.
        @endif
    </p>

    <p style="margin:0;color:#788B9A;font-size:14px;">
        Pas d'inspiration cette fois ? Vous pouvez poser un joker depuis la page de la session
        @if ($jokersLeft > 0)
            (il vous en reste {{ $jokersLeft }} cette année).
        @else
            — mais vos jokers de l'année sont déjà posés.
        @endif
    </p>

    @include('emails.partials.button', ['url' => $url, 'label' => 'Voir la session'])
@endsection
