<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Campaign;
use App\Http\Controllers\Controller;
use App\Oficina;
use App\Persona;
use App\Suscribe;
use App\Http\Resources\SuscriptosResource;
use App\Search\SuscriptosSearch;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;

class CampanasController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::query()
            ->where('pais_id', auth()->user()->idPaisPermitido)
            ->orderBy('created_at', 'desc');

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        return response()->json($query->paginate(25));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Campaign::class);

        $request->validate([
            'nombre'               => 'required|string|max:255',
            'descripcion'          => 'nullable|string',
            'tipo'                 => 'nullable|in:colecta,captacion',
            'oficina_id'           => [
                'nullable', 'exists:atl_oficinas,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $oficina = Oficina::find($value);
                        if (!$oficina || (int) $oficina->id_pais !== (int) auth()->user()->idPaisPermitido) {
                            $fail('La oficina no pertenece a tu país.');
                        }
                    }
                },
            ],
            'fecha_inicio'         => 'nullable|date',
            'fecha_fin'            => 'nullable|date',
            'activa'               => 'boolean',
            'whatsapp_link'        => 'nullable|url',
            'confirmation_message' => 'nullable|string',
        ]);

        $campana = Campaign::create($request->except('slug', 'imagen'));

        return response()->json($campana, 201);
    }

    public function update(Request $request, $id)
    {
        $campana = Campaign::findOrFail($id);
        $this->authorize('update', $campana);

        $request->validate([
            'nombre'               => 'required|string|max:255',
            'descripcion'          => 'nullable|string',
            'tipo'                 => 'nullable|in:colecta,captacion',
            'oficina_id'           => [
                'nullable', 'exists:atl_oficinas,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $oficina = Oficina::find($value);
                        if (!$oficina || (int) $oficina->id_pais !== (int) auth()->user()->idPaisPermitido) {
                            $fail('La oficina no pertenece a tu país.');
                        }
                    }
                },
            ],
            'fecha_inicio'         => 'nullable|date',
            'fecha_fin'            => 'nullable|date',
            'activa'               => 'boolean',
            'whatsapp_link'        => 'nullable|url',
            'confirmation_message' => 'nullable|string',
        ]);

        $campana->update($request->except('slug', 'imagen'));

        return response()->json($campana);
    }

    public function destroy($id)
    {
        $campana = Campaign::findOrFail($id);
        $this->authorize('delete', $campana);
        $campana->delete();

        return response()->json(null, 204);
    }

    public function storeImagen(Request $request, $id)
    {
        $campana = Campaign::findOrFail($id);
        $this->authorize('update', $campana);

        $request->validate([
            'imagen' => 'required|file|image|max:4096',
        ]);

        $path = ImageUploadService::store($request->file('imagen'), 'public/campanas');
        $campana->imagen = str_replace('public', 'storage', '/' . $path);
        $campana->save();

        return response()->json(['imagen' => $campana->imagen]);
    }

    public function suscriptos(Request $request, $id)
    {
        $filtros = ['campaign_id' => $id];

        if ($request->filled('usuario')) {
            $filtros['usuario'] = $request->usuario;
        }

        $sort = 'created_at desc';
        if ($request->filled('sort')) {
            $sort = strpos($request->sort, '|')
                ? join(' ', explode('|', $request->sort))
                : $request->sort;
        }

        $per_page = $request->filled('per_page') ? $request->per_page : 25;

        $result = SuscriptosSearch::apply($filtros, $sort, $per_page);
        $result->getCollection()->load('respuestas.pregunta');

        // REQ 1 — Detectar si el email del Subscribe ya corresponde a un usuario (Persona)
        $emails   = $result->getCollection()->pluck('mail')->filter()->values()->toArray();
        $personas = \App\Persona::whereIn('mail', $emails)
            ->select(['idPersona', 'mail', 'nombres', 'apellidoPaterno'])
            ->get()
            ->keyBy('mail');

        $result->getCollection()->each(function ($suscripcion) use ($personas) {
            $persona = $personas->get($suscripcion->mail);
            $suscripcion->setAttribute('persona_id',     $persona ? $persona->idPersona : null);
            $suscripcion->setAttribute('persona_nombre', $persona ? trim($persona->nombres . ' ' . $persona->apellidoPaterno) : null);
        });

        return response()->json($result);
    }

    public function convertir(Request $request, $suscripcionId)
    {
        $suscripcion = Suscribe::findOrFail($suscripcionId);

        if ($suscripcion->convertido) {
            return response()->json(['message' => 'Ya fue convertido en usuario.'], 422);
        }

        // Verificar que el suscripto pertenece al país del admin
        if ($suscripcion->idPais !== auth()->user()->idPaisPermitido) {
            return response()->json(['message' => 'Sin permisos.'], 403);
        }

        DB::beginTransaction();
        try {
            $persona = new Persona();
            $persona->nombres          = $suscripcion->nombre;
            $persona->apellidoPaterno  = $suscripcion->apellido;
            $persona->mail             = $suscripcion->mail;
            $persona->dni              = $suscripcion->dni;
            $persona->genero           = $suscripcion->genero;
            $persona->fechaNacimiento  = $suscripcion->fecha_nacimiento;
            $persona->telefonoMovil    = $suscripcion->telefono;
            $persona->idPais           = $suscripcion->idPais;
            $persona->idProvincia      = $suscripcion->idProvincia;
            $persona->idLocalidad      = $suscripcion->idLocalidad;
            $persona->canal_contacto   = $suscripcion->canal_contacto;
            $persona->instagram        = $suscripcion->instagram;
            $persona->password         = Hash::make(str_random(30));
            $persona->idUnidadOrganizacional = 0;
            $persona->recibirMails     = 1;
            $persona->unsubscribe_token = Uuid::generate()->string;
            $persona->idPaisPermitido  = 0;
            $persona->estadoPersona    = 'activo';
            $persona->save();

            $persona->assignRole('usuario_autenticado');

            $suscripcion->convertido  = true;
            $suscripcion->idPersona   = $persona->idPersona;
            $suscripcion->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al convertir: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'idPersona' => $persona->idPersona]);
    }
}
