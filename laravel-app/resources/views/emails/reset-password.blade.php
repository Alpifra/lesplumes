@extends('emails.layout')

@section('title', 'Choisir un nouveau mot de passe')
@section('preheader', 'Le lien pour choisir un nouveau mot de passe sur Les Plumes.')
@section('eyebrow', 'Mot de passe oublié')
@section('heading', 'Un petit oubli ?')

@section('content')
    <p style="margin:0;">
        Bonjour {{ $plume->first_name }}, vous avez demandé à choisir un nouveau mot de passe. Le bouton ci-dessous vous y mène.
    </p>

    @include('emails.partials.button', ['url' => $url, 'label' => 'Choisir un nouveau mot de passe'])

    <p style="margin:12px 0 0;color:#788B9A;font-size:14px;">
        Ce lien est valable {{ $minutes }} minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez ce mail : votre mot de passe reste inchangé.
    </p>
@endsection
