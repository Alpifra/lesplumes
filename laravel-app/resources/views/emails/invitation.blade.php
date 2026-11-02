@extends('emails.layout')

@section('title', 'Invitation à rejoindre Les Plumes')
@section('preheader', ($inviter ? "{$inviter->first_name} vous invite" : 'Vous êtes invité·e') . " à rejoindre le cercle d'écriture Les Plumes.")
@section('eyebrow', 'Invitation')
@section('heading', 'Une plume vous attend')

@section('content')
    <p style="margin:0;">
        Bonjour,<br>
        @if ($inviter)
            <strong>{{ $inviter->first_name }} {{ $inviter->last_name }}</strong> vous invite
        @else
            Vous êtes invité·e
        @endif
        à rejoindre <strong>Les Plumes</strong>, un cercle d'écriture : à chaque session un mot, un texte de chacun, et le plaisir de lire ceux des autres.
    </p>

    @if ($round)
        <p style="margin:16px 0 0;">Une session vous attend déjà, sur le mot :</p>
        @include('emails.partials.word', ['word' => $round->word])
    @endif

    @include('emails.partials.button', ['url' => $url, 'label' => 'Créer mon compte'])

    <p style="margin:12px 0 0;color:#788B9A;font-size:14px;">
        Inscrivez-vous avec cette adresse, {{ $email }}, pour retrouver votre place dans le cercle.
    </p>
@endsection

@section('footer')
    Vous recevez ce mail parce qu'une plume du cercle vous a invité·e. S'il ne vous dit rien, ignorez-le simplement.
@endsection
