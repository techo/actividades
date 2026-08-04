<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Regresión de la Fase 0 de hardening.
 *
 * C-2: endpoints que exponían PII de Persona sin autenticación. Antes respondían 200 con
 * datos; ahora deben exigir sesión autenticada (redirigen fuera para un invitado).
 */
class SecurityFase0Test extends TestCase
{
    /** @test */
    public function ajax_personas_requiere_autenticacion()
    {
        $this->get('/ajax/personas?q=a')->assertRedirect();
    }

    /** @test */
    public function ajax_coordinadores_requiere_autenticacion()
    {
        $this->get('/ajax/coordinadores?coordinador=a')->assertRedirect();
    }

    /** @test */
    public function admin_search_usuarios_requiere_autenticacion()
    {
        // Antes vivía fuera del grupo /admin; ahora está protegido.
        $this->get('/admin/ajax/search/usuarios?usuario=a')->assertRedirect();
    }
}
