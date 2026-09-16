{{-- Estilo compartido para las cartas de voluntariado (documento legal legible).
     No cambia el texto legal, solo la presentación. Se aplica envolviendo el
     contenido en <div class="doc-legal">. --}}
@push('additional_styles')
<style>
    .doc-legal {
        max-width: 760px;
        margin: 0 auto;
        padding: 0 1rem;
        text-align: left;
        color: #2b2b2b;
        font-size: 1.02rem;
        line-height: 1.75;
    }
    .doc-legal .doc-logo {
        display: block;
        margin: 0 auto 1.75rem;
    }
    .doc-legal .doc-title,
    .doc-legal h1 {
        font-size: 1.8rem;
        font-weight: 700;
        text-align: center;
        line-height: 1.25;
        margin: 0 0 .5rem;
    }
    .doc-legal .doc-subtitle {
        text-align: center;
        color: #6c757d;
        margin: 0 0 2.25rem;
    }
    .doc-legal h2 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 2.5rem 0 .85rem;
        padding-bottom: .4rem;
        border-bottom: 1px solid #e9ecef;
    }
    .doc-legal h4,
    .doc-legal h5 {
        font-size: 1.12rem;
        font-weight: 700;
        margin: 1.9rem 0 .6rem;
    }
    .doc-legal p {
        margin: 0 0 1.1rem;
    }
    .doc-legal ol,
    .doc-legal ul {
        padding-left: 1.5rem;
        margin: 0 0 1.35rem;
    }
    .doc-legal li {
        margin-bottom: .65rem;
    }
    .doc-legal a {
        text-decoration: underline;
    }
    .doc-legal .doc-note {
        color: #6c757d;
    }
    .doc-legal hr {
        margin: 2.5rem 0;
    }
    @media (max-width: 575px) {
        .doc-legal {
            font-size: 1rem;
            line-height: 1.7;
        }
        .doc-legal .doc-title,
        .doc-legal h1 {
            font-size: 1.45rem;
        }
        .doc-legal h2 {
            font-size: 1.2rem;
        }
    }
</style>
@endpush
