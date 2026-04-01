@extends('layouts.site')

@section('title', 'Contact — Khayma')
@section('description', 'Contactez l\'équipe Khayma. Formulaire de contact et prise de rendez-vous.')

@section('styles')
<style>
    .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; padding: 4rem 0; max-width: 1100px; margin: 0 auto; }
    .contact-form label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--dark-3); margin-bottom: 0.35rem; }
    .contact-form input, .contact-form textarea, .contact-form select { width: 100%; padding: 0.75rem 1rem; font-size: 0.9rem; border: 1px solid #E2E8F0; font-family: var(--font); transition: border-color 0.2s; background: var(--white); }
    .contact-form input:focus, .contact-form textarea:focus { outline: none; border-color: var(--green); }
    .contact-form .form-group { margin-bottom: 1.25rem; }
    .contact-form textarea { min-height: 140px; resize: vertical; }
    .contact-submit { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--green); color: var(--white); padding: 0.85rem 2rem; font-size: 0.95rem; font-weight: 700; border: none; cursor: pointer; font-family: var(--font); transition: background 0.2s; }
    .contact-submit:hover { background: var(--green-dark); }
    .contact-info { display: flex; flex-direction: column; gap: 2rem; }
    .contact-info-item { display: flex; gap: 1rem; align-items: flex-start; }
    .contact-info-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: var(--green-light); color: var(--green); font-size: 1.1rem; flex-shrink: 0; }
    .contact-info-text h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 0.25rem; }
    .contact-info-text p { color: var(--gray); font-size: 0.9rem; }
    .error-text { color: #EF4444; font-size: 0.8rem; margin-top: 0.25rem; }
    @media (max-width: 768px) { .contact-grid { grid-template-columns: 1fr; gap: 2rem; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="section-header" style="margin-bottom:0">
        <span class="section-badge green">Contact</span>
        <h1 class="section-title" style="color:var(--white)">Parlons de votre <span class="accent-green">projet</span></h1>
        <p class="section-sub" style="color:rgba(255,255,255,0.5)">Une question ? Un besoin spécifique ? Notre équipe vous répond rapidement.</p>
    </div>
</div>

<div class="page-content">
    <div class="section-container" style="max-width:1280px">
        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        <div class="contact-grid">
            <form class="contact-form" method="POST" action="{{ route('site.contact.store') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Nom complet *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="subject">Sujet</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}">
                    @error('subject') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required>{{ old('message') }}</textarea>
                    @error('message') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="contact-submit">
                    <i class="fas fa-paper-plane"></i> Envoyer le message
                </button>
            </form>

            <div class="contact-info">
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="contact-info-text">
                        <h4>Email</h4>
                        <p>contact@khayma.com</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-phone"></i></div>
                    <div class="contact-info-text">
                        <h4>Téléphone</h4>
                        <p>+221 XX XXX XX XX</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-info-text">
                        <h4>Adresse</h4>
                        <p>Dakar, Sénégal</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fab fa-whatsapp"></i></div>
                    <div class="contact-info-text">
                        <h4>WhatsApp</h4>
                        <p>Discutez avec nous en temps réel</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
