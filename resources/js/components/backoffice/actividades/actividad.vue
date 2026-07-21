<template>
    <div>
        <!-- informacion general -->
        <div class="box">
            <div class="row text-center">
                    <div v-if="estadoInscripcion" class="alert alert-info" role="alert" >
                        {{ $t('backend.open_registrations') }}
                    </div>
                    <div v-else class="alert alert-danger" role="alert" >
                        {{ $t('backend.closed_registrations') }}
                    </div>

                    <div v-if="estadoEvaluaciones" class="alert alert alert-warning" role="alert" >
                        {{ $t('backend.open_evaluations') }}
                    </div>

                    <div v-if="(!estadoPago && actividad.pago)" class="alert alert alert-danger" role="alert" >
                        {{ $t('backend.payment_date_expired') }}
                    </div>
                </div>
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('backend.general_information') }}</h3>
                <!-- <div v-show="edicion"  class="row text-center">
                    <input type="checkbox" v-model="advanceConfigutation" :disabled="!edicion"> {{ $t('backend.advance_configuration') }}
                </div> -->
            </div>
            <div class="box-body">
                
                
                
                <div class="row m-2">
                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.nombreActividad }" >
                            <label for="nombreActividad">{{ $t('backend.name') }}</label>
                            <input name="nombreActividad" type="text" class="form-control" v-model="actividad.nombreActividad"required :disabled="!edicion"> 
                            <span class="help-block">{{ errors.nombreActividad }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div :class="{ 'form-group': true, 'has-error': errors.idPais }" >
                            <label for="pais">{{ $t('backend.country') }}</label>
                            <select name="idPais" class="form-control" v-model="actividad.idPais" required 
                            @change="getProvincias($event);getOficinas($event);actividad.idProvincia=null;actividad.idOficina=null; actividad.idLocalidad=null;" 
                            :disabled="!edicion" >
                                <option v-text="pais.nombre" v-bind:value="pais.id" v-for="pais in paises" ></option>
                            </select>
                            <span class="help-block">{{ errors.idPais }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div :class="{ 'form-group': true, 'has-error': errors.idOficina }" >
                            <label for="oficina">{{ $t('backend.office') }}</label>
                            <select name="idOficina"  @change="getComunidades($event.target.value);getEquipos();" class="form-control" v-model="actividad.idOficina" required :disabled="!edicion">
                                <option v-text="oficina.nombre" v-bind:value="oficina.id" v-for="oficina in oficinas" ></option>
                            </select>
                            <span class="help-block">{{ errors.idOficina }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div :class="{ 'form-group': true, 'has-error': errors.idEquipo }" >
                            <label for="equipo">{{ $t('backend.team') }}</label>
                            <select name="idEquipo" class="form-control" v-model="actividad.idEquipo" required :disabled="!edicion">
                                <option value="" ></option>
                                <option v-text="equipo.nombre" v-bind:value="equipo.idEquipo" v-for="equipo in equipos" ></option>
                            </select>
                            <span class="help-block">{{ errors.idEquipo }}</span>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="categoria">{{ $t('backend.category') }}</label>
                            <select name="idCategoria" @change="getTipos($event.target.value)"  required class="form-control" :disabled="!edicion" >
                                <option v-bind:value="categoria.id" v-for="categoria in categorias" :selected="actividad.tipo.idCategoria == categoria.id">{{ $t('frontend.' + categoria.nombre) }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.idTipo }" >
                            <label for="tipo">{{ $t('backend.type') }}</label>
                            <select name="idTipo" class="form-control" v-model="actividad.idTipo" required :disabled="!edicion" >
                                <option v-bind:value="tipo.idTipo" v-for="tipo in tipos" >{{ tipo.nombre_localizado || tipo.nombre }}</option>
                            </select>
                            <span class="help-block">{{ errors.idTipo }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="vida_escuela">{{ $t('backend.vida_escuela') }}</label>
                            <select name="vida_escuela" class="form-control" v-model="actividad.vida_escuela" :disabled="!edicion" >
                                <option value="1" :selected="actividad.vida_escuela == 1" >{{ $t('backend.yes') }}</option>
                                <option value="0" :selected="actividad.vida_escuela == 0" selected>{{ $t('backend.no') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="estado">{{ $t('backend.activity_status') }}</label>
                            <select name="estadoConstruccion" class="form-control" v-model="actividad.estadoConstruccion" required :disabled="!edicion" >
                                <option value="Abierta" :selected="actividad.estadoConstruccion == 'Abierta'" >{{ $t('backend.open') }}</option>
                                <option value="Cerrada" :selected="actividad.estadoConstruccion == 'Cerrada'" >{{ $t('backend.closed') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="inscripcionInterna">{{ $t('backend.activity_visibility') }}</label>
                            <select name="inscripcionInterna" class="form-control" v-model="actividad.inscripcionInterna" required :disabled="!edicion" >
                                <option value="1" :selected="actividad.inscripcionInterna == 1" >{{ $t('backend.private') }}</option>
                                <option value="0" :selected="actividad.inscripcionInterna == 0" >{{ $t('backend.public') }}</option>
                            </select>
                        </div>
                    </div>

                </div>

                <!-- Fechas / Datas / Dates -->
                <div class="box-header with-border bg-primary dates-section-header">
                    <h3 class="box-title">{{ $t('backend.dates') }}</h3>
                </div>

                <div class="row dates-cards-row">

                    <!-- Card: Actividad -->
                    <div class="col-md-4">
                        <div class="dates-card">
                            <div class="dates-card-header dates-card-header--activity">
                                <span class="dates-card-icon">📅</span>
                                <span class="dates-card-title">{{ $t('backend.dates_activity') }}</span>
                            </div>
                            <div class="dates-card-body">
                                <div v-if="statusActividadChip" class="dates-status" :class="'dates-status--' + statusActividadChip.variant">
                                    {{ statusActividadChip.label }}
                                </div>
                                <div class="dates-show-checkbox">
                                    <input type="checkbox" v-model="actividad.show_dates" :disabled="!edicion"> {{ $t('backend.show_dates') }}
                                </div>
                                <label>{{ $t('backend.activity_start_date') }}</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaInicio }">
                                    <input v-model="fechas.fechaInicio" type="date" @change="fechas.fechaFin=fechas.fechaInicio;" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                                    <span class="help-block">{{ errors.fechaInicio }}</span>
                                    <span class="input-group-addon">
                                        <input v-model="horas.fechaInicio" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                                    </span>
                                </div>
                                <label>{{ $t('backend.activity_end_date') }}</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaFin }">
                                    <input v-model="fechas.fechaFin" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                                    <span class="help-block">{{ errors.fechaFin }}</span>
                                    <span class="input-group-addon">
                                        <input v-model="horas.fechaFin" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Inscripciones -->
                    <div class="col-md-4">
                        <div class="dates-card" :class="{ 'dates-card--error': errorFinInscripcionesPostInicio }">
                            <div class="dates-card-header dates-card-header--registrations">
                                <span class="dates-card-icon">📝</span>
                                <span class="dates-card-title">{{ $t('backend.dates_registrations') }}</span>
                                <span v-if="!modoManualInscripciones" class="dates-badge dates-badge--auto">{{ $t('backend.automatic') }}</span>
                                <span v-else class="dates-badge dates-badge--manual">{{ $t('backend.manual') }}</span>
                                <span v-if="errorFinInscripcionesPostInicio" class="dates-card-error-icon" title="">⚠️</span>
                            </div>
                            <div class="dates-card-body">
                                <div v-if="statusInscripcionesChip" class="dates-status" :class="'dates-status--' + statusInscripcionesChip.variant">
                                    {{ statusInscripcionesChip.label }}
                                </div>
                                <div v-if="edicion" class="dates-toggle">
                                    <button v-if="!modoManualInscripciones" @click.prevent="modoManualInscripciones = true" class="btn btn-link btn-xs dates-toggle-btn">
                                        {{ $t('backend.edit_manually') }}
                                    </button>
                                    <button v-else @click.prevent="restaurarInscripcionesAuto()" class="btn btn-link btn-xs dates-toggle-btn">
                                        ↩ {{ $t('backend.restore_automatic') }}
                                    </button>
                                </div>
                                <label>{{ $t('backend.registrations_start') }}</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaInicioInscripciones }">
                                    <input v-model="fechas.fechaInicioInscripciones" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion || !modoManualInscripciones">
                                    <span class="help-block">{{ errors.fechaInicioInscripciones }}</span>
                                    <span class="input-group-addon">
                                        <input v-model="horas.fechaInicioInscripciones" type="time" required style="border: none; height: 20px;" :disabled="!edicion || !modoManualInscripciones">
                                    </span>
                                </div>
                                <label>{{ $t('backend.ending') }}</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaFinInscripciones || errorFinInscripcionesPostInicio }">
                                    <input v-model="fechas.fechaFinInscripciones" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion || !modoManualInscripciones">
                                    <span class="help-block">{{ errors.fechaFinInscripciones }}</span>
                                    <span class="input-group-addon">
                                        <input v-model="horas.fechaFinInscripciones" type="time" required style="border: none; height: 20px;" :disabled="!edicion || !modoManualInscripciones">
                                    </span>
                                </div>
                                <p v-if="errorFinInscripcionesPostInicio" class="dates-validation-error">
                                    {{ $t('backend.validation_fin_inscripciones_after_inicio') }}
                                </p>
                                <p v-if="!modoManualInscripciones" class="dates-hint">{{ $t('backend.dates_auto_hint_registrations') }}</p>
                                <hr class="dates-card-divider">
                                <div class="form-group">
                                    <label>{{ $t('backend.slots') }}</label>
                                    <input type="number" min="0" class="form-control" v-model="actividad.limiteInscripciones" required :disabled="!edicion">
                                    <p class="help-block">{{ $t('backend.unlimited_slots') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Evaluaciones -->
                    <div class="col-md-4">
                        <div class="dates-card">
                            <div class="dates-card-header dates-card-header--evaluations">
                                <span class="dates-card-icon">⭐</span>
                                <span class="dates-card-title">{{ $t('backend.dates_evaluations') }}</span>
                                <span v-if="!modoManualEvaluaciones" class="dates-badge dates-badge--auto">{{ $t('backend.automatic') }}</span>
                                <span v-else class="dates-badge dates-badge--manual">{{ $t('backend.manual') }}</span>
                            </div>
                            <div class="dates-card-body">
                                <div v-if="statusEvaluacionesChip" class="dates-status" :class="'dates-status--' + statusEvaluacionesChip.variant">
                                    {{ statusEvaluacionesChip.label }}
                                </div>
                                <div v-if="edicion" class="dates-toggle">
                                    <button v-if="!modoManualEvaluaciones" @click.prevent="modoManualEvaluaciones = true" class="btn btn-link btn-xs dates-toggle-btn">
                                        {{ $t('backend.edit_manually') }}
                                    </button>
                                    <button v-else @click.prevent="restaurarEvaluacionesAuto()" class="btn btn-link btn-xs dates-toggle-btn">
                                        ↩ {{ $t('backend.restore_automatic') }}
                                    </button>
                                </div>
                                <div>
                                    <label>{{ $t('backend.evaluations_start') }}</label>
                                    <div :class="{ 'input-group': true, 'has-error': errors.fechaInicioEvaluaciones }">
                                        <input v-model="fechas.fechaInicioEvaluaciones" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion || !modoManualEvaluaciones">
                                        <span class="help-block">{{ errors.fechaInicioEvaluaciones }}</span>
                                        <span class="input-group-addon">
                                            <input v-model="horas.fechaInicioEvaluaciones" type="time" required style="border: none; height: 20px;" :disabled="!edicion || !modoManualEvaluaciones">
                                        </span>
                                    </div>
                                    <label>{{ $t('backend.ending') }}</label>
                                    <div :class="{ 'input-group': true, 'has-error': errors.fechaFinEvaluaciones }">
                                        <input v-model="fechas.fechaFinEvaluaciones" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion || !modoManualEvaluaciones">
                                        <span class="help-block">{{ errors.fechaFinEvaluaciones }}</span>
                                        <span class="input-group-addon">
                                            <input v-model="horas.fechaFinEvaluaciones" type="time" required style="border: none; height: 20px;" :disabled="!edicion || !modoManualEvaluaciones">
                                        </span>
                                    </div>
                                    <p v-if="!modoManualEvaluaciones" class="dates-hint">{{ $t('backend.dates_auto_hint_evaluations') }}</p>
                                </div>
                                <hr class="dates-card-divider">
                                <div class="form-group">
                                    <label>{{ $t('backend.evaluation_link') }}
                                        <a v-if="actividad.linkEvaluacion" :href="actividad.linkEvaluacion+'viewform'" target="_blank"> {{ $t('backend.view') }}</a>
                                    </label>
                                    <input v-if="edicion" type="text" class="form-control" v-model="actividad.linkEvaluacion" :disabled="!edicion">
                                    <span class="help-block">{{ errors.linkEvaluacion }}</span>
                                    <p class="help-block">{{ $t('backend.evaluation_link_description') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <br>

                <div class="row">
                    <div class="col-md-12">
                        <div :class="{ 'form-group': true, 'has-error': errors.descripcion }" >
                            <label for="descripcion">{{ $t('backend.description') }}</label>
                            <tinymce-editor 
                                v-model="actividad.descripcion" 
                                :init="{
                                    menubar: 'false',
                                    file_picker_callback: tiny_mce_filemanager_callback,
                                    relative_urls: false,
                                    resize: true,
                                }"
                                toolbar="undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image" 
                                plugins="paste autoresize image preview paste link"
                                :disabled="!edicion"
                            ></tinymce-editor>
                            <span class="help-block">{{ errors.descripcion }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Ubicacion -->
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('backend.ubication') }}</h3>
                <input class="bg-primary" type="checkbox" v-model="actividad.show_location" :disabled="!edicion"> 
                    {{ $t('backend.show_location') }}
                </input>
            </div>
            <div class="box-body">

                <span class="help-block" v-show="virtual==true">{{ $t('backend.meeting_location') }}</span>
                <span class="help-block" v-show="virtual==false">{{ $t('backend.activity_location_description') }}</span>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="comunidades">{{ $t('backend.comunidades') }}</label>
                            <vue-tags-input
                                v-model="tagComunidades"
                                :tags="comunidadesTags"
                                :disabled="!edicion"
                                add-only-from-autocomplete
                                :autocompleteItems="filteredComunidadTags"
                                placeholder=""
                                @tags-changed="newTags => comunidadesTags = newTags"
                            />

                            <p class="help-block">
                            
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.lugar }" >
                            <label for="lugar">{{ $t('backend.location_medium') }} </label>
                            <input id="lugar" name="lugar" type="text" class="form-control" v-model="actividad.lugar" required
                            :disabled="!edicion" >
                            <span class="help-block">{{ errors.lugar }}</span>
                        </div>
                    </div>

                    <div class="col-md-3" v-show="virtual==false">
                        <div :class="{ 'form-group': true, 'has-error': errors.idProvincia }" >
                            <label for="provincia">{{ $t('backend.province') }}</label>
                            <select name="idProvincia" class="form-control" v-model="actividad.idProvincia" required @change="getLocalidades($event)" :disabled="!edicion">
                                <option v-text="provincia.provincia" v-bind:value="provincia.id" v-for="provincia in provincias" ></option>
                            </select>
                            <span class="help-block">{{ errors.idProvincia }}</span>
                        </div>
                    </div>

                    <div class="col-md-3" v-show="virtual==false">
                        <div :class="{ 'form-group': true, 'has-error': errors.idLocalidad }" >
                            <label for="localidad">{{ $t('backend.location') }}</label>
                            <select name="idLocalidad" class="form-control" v-model="actividad.idLocalidad" required :disabled="!edicion">
                                <option v-text="localidad.localidad" v-bind:value="localidad.id" v-for="localidad in localidades" ></option>
                            </select>
                            <span class="help-block">{{ errors.idLocalidad }}</span>
                        </div>
                    </div>

                </div>

            </div>
           <!--  <div class="box-footer">
                <span class="help-block text-light-blue"><i class="fa  fa-exclamation"></i> El sistema carga automáticamente un punto de encuentro que coincide con la ubicación de la actividad. En caso de ser necesario, se puede editar o borrar y cargar otros puntos de encuentro según la lógica de la actividad.</span>
            </div> -->
        </div>

        <!-- confirmacion y pago -->
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('backend.confirmation') }}</h3>

            </div>

            <div class="box-body">
                <p class="help-block">{{ $t('backend.confirmation_intro') }}</p>

                <!-- Selector de modo: 4 tarjetas que setean pago + confirmacion -->
                <div class="modo-cards row">
                    <div class="col-md-6" v-for="modo in modosConfirmacion" :key="modo.key">
                        <div class="modo-card"
                             :class="{
                                'modo-card--active': modoActual === modo.key,
                                'modo-card--disabled': !edicion
                             }"
                             @click="seleccionarModo(modo.key)">
                            <span class="modo-card__icon">{{ modo.icon }}</span>
                            <div class="modo-card__body">
                                <strong>{{ modo.title }}</strong>
                                <p>{{ modo.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bloque de pago: sólo visible en "Por donación / Pago" y "Mixto" -->
                <div v-show="actividad.pago == 1">

                    <h4 class="pago-section-title">{{ $t('backend.amount_details') }}</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div :class="{ 'form-group': true, 'has-error': errors.montoMin }" >
                                <label for="">{{ $t('backend.amount') }} *</label>
                                <input type="number" class="form-control" v-model="actividad.montoMin" :disabled="!edicion" >
                                <span class="help-block">{{ errors.montoMin }}</span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div :class="{ 'form-group': true, 'has-error': errors.fechaLimitePago }" >
                                <label for="">{{ $t('backend.payment_deadline') }}</label>
                                <input v-model="fechas.fechaLimitePago" type="date" class="form-control" :disabled="!edicion">
                                <p class="help-block">
                                    {{ $t('backend.registration_deadline_instruction') }}
                                </p>
                                <span class="help-block">{{ errors.fechaLimitePago }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="exencion-box"
                                   :class="{ 'exencion-box--active': actividad.permite_exencion, 'exencion-box--disabled': !edicion }">
                                <input type="checkbox" v-model="actividad.permite_exencion" :disabled="!edicion">
                                <span>
                                    <strong>{{ $t('backend.allow_scholarship_request') }}</strong>
                                    <small>{{ $t('backend.allow_scholarship_request_help') }}</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <h4 class="pago-section-title">{{ $t('backend.payment_methods_enabled') }}</h4>
                    <div class="row metodos-row">
                        <div class="col-md-4">
                            <label class="metodo-card"
                                   :class="{ 'metodo-card--active': actividad.metodos_pago.transferencia, 'metodo-card--disabled': !edicion }">
                                <input type="checkbox" v-model="actividad.metodos_pago.transferencia" :disabled="!edicion">
                                <span>
                                    <strong>🏦 {{ $t('backend.method_bank_transfer') }}</strong>
                                    <small>{{ $t('backend.method_bank_transfer_help') }}</small>
                                </span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="metodo-card"
                                   :class="{ 'metodo-card--active': actividad.metodos_pago.link_pix, 'metodo-card--disabled': !edicion }">
                                <input type="checkbox" v-model="actividad.metodos_pago.link_pix" :disabled="!edicion">
                                <span>
                                    <strong>🔗 {{ $t('backend.method_link_pix') }}</strong>
                                    <small>{{ $t('backend.method_link_pix_help') }}</small>
                                </span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="metodo-card"
                                   :class="{ 'metodo-card--active': actividad.metodos_pago.tarjeta, 'metodo-card--disabled': !edicion }">
                                <input type="checkbox" v-model="actividad.metodos_pago.tarjeta" :disabled="!edicion">
                                <span>
                                    <strong>💳 {{ $t('backend.method_card') }}</strong>
                                    <small>{{ $t('backend.method_card_help') }}</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Datos bancarios: sólo si Transferencia está habilitada -->
                    <div class="row" v-show="actividad.metodos_pago.transferencia">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.descripcionPago }" >
                                <label for="">{{ $t('backend.bank_transfer_data') }}</label>
                                <p class="help-block">{{ $t('backend.bank_transfer_data_help') }}</p>
                                <tinymce-editor
                                    v-model="actividad.descripcionPago"
                                    :init="{
                                        menubar: 'false',
                                        file_picker_callback: tiny_mce_filemanager_callback,
                                        relative_urls: false,
                                        resize: true,
                                    }"
                                    toolbar="undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
                                    plugins="paste autoresize image preview paste link"
                                    :disabled="!edicion"
                                ></tinymce-editor>
                                <span class="help-block">{{ errors.descripcionPago }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- URL del link de pago: sólo si Pago por Link / Pix está habilitado -->
                    <div class="row" v-show="actividad.metodos_pago.link_pix">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.linkPago }" >
                                <label for="">
                                    🔗 {{ $t('backend.payment_link_url') }}
                                    <a v-if="actividad.linkPago" :href="actividad.linkPago" target="_blank" class="ml-2 small">
                                        <i class="fas fa-external-link-alt"></i> {{ $t('backend.view') }}
                                    </a>
                                </label>
                                <input type="url" class="form-control" v-model="actividad.linkPago" :disabled="!edicion"
                                    placeholder="https://" >
                                <p class="help-block">{{ $t('backend.payment_link_url_help') }}</p>
                                <span class="help-block">{{ errors.linkPago }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-md-12">
                        <div :class="{ 'form-group': true, 'has-error': errors.mensajeInscripcion }" >
                            <label for="mensajeInscripcion">{{ $t('backend.message') }} *</label>
                            <p class="help-block">{{ $t('backend.confirmation_message') }}</p>
                            <textarea name="mensajeInscripcion" v-model="actividad.mensajeInscripcion" class="form-control" required :disabled="!edicion" >{{ $t('backend.activity_confirmation_message') }}</textarea>
                            <span class="help-block">{{ errors.mensajeInscripcion }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ficha medica -->
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('backend.medical_form') }}</h3>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="require_ficha_medica">{{ $t('backend.medical_form_required') }}</label>
                            <select name="requiere_ficha_medica" class="form-control" v-model="actividad.requiere_ficha_medica" required :disabled="!edicion">
                                <option value="1" :selected="actividad.requiere_ficha_medica == 1" >{{ $t('backend.yes') }}</option>
                                <option value="0" :selected="actividad.requiere_ficha_medica == 0" >{{ $t('backend.no') }}</option>
                            </select>

                            <p v-show="actividad.requiere_ficha_medica == 1" class="help-block">{{ $t('backend.medical_form_upload_instruction') }}</p>
                        </div>
                    </div>
                    <div v-show="actividad.requiere_ficha_medica == 1" class="col-md-10">
                        <div v-for="(valor, index) in fichaMedicaCampos " class="col-md-4">
                            <div class="">
                                <label>
                                <input class="col-md-1" :name="valor" v-model="fichaMedicaCampos[index]" type="checkbox" :disabled="!edicion" />
                                <span class="col-md-11 font-weight-light">{{ $t('frontend.'+ index) }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voluntario y datos pedidos -->
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('backend.volunteer') }}</h3>
                
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="applicable_roles">{{ $t('backend.applicable_roles') }}</label>
                            <p class="help-block">
                                {{ $t('backend.applicable_roles_description') }}
                                {{ $t('backend.press_enter_between_each') }}
                            </p>
                            <vue-tags-input
                                v-model="tag"
                                :tags="rolesTags"
                                :disabled="!edicion"
                                :autocompleteItems="filteredRolesTags"
                                :add-only-from-autocomplete="true"
                                placeholder=""
                                @tags-changed="newTags => rolesTags = newTags"
                            />

                            <p class="help-block">
                                {{ $t('backend.blank_role_field_message') }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="registration_type">{{ $t('backend.registration_type') }}</label>
                            <p class="help-block">
                                {{ $t('backend.registration_channels_description') }}
                            </p>
                            <vue-tags-input
                                v-model="tag2"
                                :tags="tipoInscriptosTags"
                                :disabled="!edicion"
                                :autocompleteItems="filteredTipoInscriptosTags"
                                :add-only-from-autocomplete="true"
                                placeholder=""
                                @tags-changed="newTags => tipoInscriptosTags = newTags"
                            />
                            <p class="help-block">
                                {{ $t('backend.blank_channel_field_message') }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="requiere_estudios">{{ $t('frontend.estudios') }}</label>
                            <p class="help-block">
                                {{ $t('backend.estudios_description') }}
                            </p>
                            <select name="requiere_estudios" class="form-control" v-model="actividad.requiere_estudios" required :disabled="!edicion">
                                <option value="1" :selected="actividad.requiere_estudios == 1" >{{ $t('backend.yes') }}</option>
                                <option value="0" :selected="actividad.requiere_estudios == 0" >{{ $t('backend.no') }}</option>
                            </select>
                            <!-- <p class="help-block">
                                {{ $t('backend.blank_channel_field_message') }}
                            </p> -->
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="chatWhatsapp">{{ $t('backend.whatsapp_group_chat') }}</label>
                            <p class="help-block">
                                {{ $t('backend.whatsapp_group_chat_url') }}
                            </p>
                            <input type="text" class="form-control" v-model="actividad.chat_grupal_whatsapp" 
                            :disabled="!edicion" id="chatWhatsapp">
                            <span class="help-block">{{ errors.chat_grupal_whatsapp }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visualizacion -->
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('backend.visualization') }}</h3>
                
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="actividades_tags">{{ $t('backend.actividades_tags') }}</label>
                            <p class="help-block">
                                {{ $t('backend.tags_description') }}
                                {{ $t('backend.press_enter_between_each') }}
                            </p>
                            <vue-tags-input
                                v-model="actividadTagSelected"
                                :tags="actividadesTags"
                                :disabled="!edicion"
                                :autocompleteItems="filteredActividadTags"
                                :add-only-from-autocomplete="true"
                                placeholder=""
                                @tags-changed="newTags => actividadesTags = newTags"
                            />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="imagen_tarjeta">{{ $t('backend.imagen_tarjeta') }}</label>
                            <p class="help-block">
                                {{ $t('backend.imagen_tarjeta_description') }}
                            </p>
                            <div v-show="!openPhotoEdit">
                                <img v-if="actividad.imagen_tarjeta != null && actividad.imagenNew == null"
                                 v-bind:style="{ borderRadius: '15%', width: '15rem', height: '8.4rem' }"  :src="actividad.imagen_tarjeta" alt="Foto">
                                <img v-else-if="actividad.imagenNew != null" :src="croppedImagenTarjetaUrl" v-bind:style="{ borderRadius: '15%', width: '15rem', height: '8.4rem' }" alt="User Image">
                            
                                <button  v-if="edicion"  class="btn btn-light btn-circle edit-button mt-3 position-absolute top-50 start-50 translate-middle" @click="selectPhoto">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </div>
                            <photoEdit :openPhotoEdit="openPhotoEdit" :photoPerfil="actividad.imagen_tarjeta"
                            :ratio="idCategoria == '6' ? 9/16 : 4/3" @updatePhoto="updatePhoto">
                            </photoEdit >
                            
                        </div>
                        
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="imagen_destacada">{{ $t('backend.destacada') }}</label>
                            <p class="help-block">
                                {{ $t('backend.destacada_description') }}
                            </p>

                            <div v-show="!openPhotoEditDestacada">
                                <img v-if="actividad.imagen_destacada != null && actividad.imagenDestacadaNew == null"
                                v-bind:style="{borderRadius:'15%' , maxWidth:'24rem', maxHeight:'8rem', minWidth:'24rem', minHeight:'10rem'} "
                                :src="actividad.imagen_destacada" alt="Foto">
                                <img v-else-if="actividad.imagenDestacadaNew != null" :src="croppedImagenDestacadaUrl" 
                                v-bind:style="{borderRadius:'15%' , maxWidth:'24rem', maxHeight:'8rem', minWidth:'24rem', minHeight:'10rem'} " alt="User Image">
                            
                                <button  v-if="edicion"  class="btn btn-light btn-circle edit-button mt-3 position-absolute top-50 start-50 translate-middle" @click="selectPhotoDestacada">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </div>
                        <photoEdit :openPhotoEdit="openPhotoEditDestacada" :photoPerfil="actividad.imagen_destacada" :ratio="5/1" @updatePhoto="updatePhotoDestacada">
                            </photoEdit >

                            <span class="help-block">{{ errors.imagen_destacada }}</span>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- terminos y condiciones -->
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('backend.terms_and_conditions') }}</h3>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="requiere_acuerdo_especifico">{{ $t('backend.requiere_acuerdo_especifico') }}</label>
                            <p class="help-block">
                                {{ $t('backend.requiere_acuerdo_especifico_description') }}
                            </p>
                            <input class="form-control" v-model="actividad.acuerdo_especifico_url" type="text" :disabled="!edicion" />
                            <p class="help-block">
                                {{ $t('backend.blank_channel_field_message') }}
                            </p>
        
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="requiere_acuerdo_menores">{{ $t('backend.requiere_acuerdo_menores') }}</label>
                            <p class="help-block">
                                {{ $t('backend.requiere_acuerdo_menores_description') }}
                            </p>
                            <input class="form-control" v-model="actividad.acuerdo_menores_url" type="text" :disabled="!edicion" />
                            <p class="help-block">
                                {{ $t('backend.blank_channel_field_message') }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="seguimiento_google">{{ $t('backend.google_tracking_code') }}</label>
                            <input type="text" id="seguimiento_google" class="form-control" v-model="actividad.seguimiento_google"
                            :disabled="!edicion" >
                            <p class="help-block">{{ $t('backend.google_tracking_code_description') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box" v-show="edicion == false">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $t('backend.audit') }}</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    {{ $t('backend.last_modified') }}: {{ actividad.fechaModificacion }}&nbsp;<a class="btn btn-primary btn-sm" @click="cargarAuditoria(actividad.idActividad)">{{ $t('backend.view_audit') }}</a>
                </p>
            </div>
        </div>

    </div>
</template>

<script>
    import editor from '@tinymce/tinymce-vue'
    import vSwitch from 'vue-switches';

    import 'tinymce/tinymce'

    // Theme
    import 'tinymce/themes/silver/theme'

    // Plugins
    import 'tinymce/plugins/paste'
    import 'tinymce/plugins/autoresize'
    import 'tinymce/plugins/image'
    import 'tinymce/plugins/preview'
    import 'tinymce/plugins/paste'
    import 'tinymce/plugins/link'
    import VueTagsInput from '@johmun/vue-tags-input';


    import photoEdit from '../../common/photoCropper';    

    export default {
        name: "actividad",
        props: {'id': {}, 'disabled': {default: false, type: Boolean} },
        components: { 'tinymce-editor': editor, vSwitch, VueTagsInput , photoEdit},
        data() {
            return {
                tag: '',
                actividadTagSelected: '',
                tag2: '',
                tagComunidades: '',
                rolesTags: [],
                actividadesTags: [],
                tipoInscriptosTags:  [],
                comunidadesTags:  [],
                openPhotoEdit: false,
                openPhotoEditDestacada: false,
                croppedImagenTarjetaUrl: '',
                croppedImagenDestacadaUrl: '',

                autocompleteActividadTags: [{
                        text: 'Nuevos Voluntarios',
                    }, {
                        text: 'Hito Anual',
                    }, {
                        text: 'Equipos',
                    }, {
                        text: 'Últimos Cupos',
                    }],

                autocompleteComunidadesTags: [],

                fichaMedicaCampos:{
                    'contacto_emergencia' : false,
                    'grupo_sanguinieo' : false,
                    'cobertura_medica': false,
                    'ficha_alergias' : false,
                    'ficha_alimentacion' : false,
                    'documento_identidad' : false,
                    'vacunacion_covid' : false,
                    'enfermedades_preexistentes' : false,
                },
                estadoInscripcion: false,
                estadoEvaluaciones: false,
                estadoPago: false,
                actividad: {
                    nombreActividad: null,
                    descripcion: '',
                    estadoConstruccion: 'Abierta',
                    confirmacion: 0,
                    pago: 0,

                    idTipo: null,
                    idOficina: null,
                    idEquipo: null,

                    vida_escuela: 0,
                    calculaFecha: false,
                    fechaInicio: null,
                    fechaFin: null,

                    lugar: '',
                    idPais: null,
                    idProvincia: null,
                    idLocalidad: null,

                    limiteInscripciones: 0,
                    inscripcionInterna: 0,
                    seguimiento_google: null,

                    requiere_ficha_medica: 0,
                    ficha_medica_campos: {},
                    requiere_estudios: 0,

                    acuerdo_especifico_url: null,
                    acuerdo_menores_url: null,

                    descripcionPago: null,
                    linkPago: null,
                    permite_exencion: false,
                    metodos_pago: {
                        transferencia: false,
                        link_pix: false,
                        tarjeta: false,
                    },

                    show_dates: true,
                    show_location: true,

                    tipo : {
                        idCategoria: 1
                    },

                    chat_grupal_whatsapp: null,
                    imagenNew: null,
                    imagenDestacadaNew: null,
                    imagenDestacada: null,

                },
                fechas: {
                    fechaInicio: null,
                    fechaFin: null,

                    fechaInicioInscripciones: null,
                    fechaFinInscripciones: null,

                    fechaInicioEvaluaciones: null,
                    fechaFinEvaluaciones: null,

                    fechaLimitePago: null,
                },
                horas: {
                    fechaInicio: "19:00:00",
                    fechaFin: "21:00:00",

                    fechaInicioInscripciones: null,
                    fechaFinInscripciones: null,

                    fechaInicioEvaluaciones: null,
                    fechaFinEvaluaciones: null,

                    fechaLimitePago: null,
                },
                errors: {},
                paises: [],
                provincias: [],
                localidades: [],
                oficinas: [],
                equipos: [],
                tipos: [],
                categorias: [],
                comunidades: [],
                modoManualInscripciones: false,
                modoManualEvaluaciones: false,
                edicion: false,
                virtual: false,
                updateArchivo: false,
                updateDestacada: false,
                idCategoria: 0,
            }
        },
        created() {
            this.edicion = !this.disabled;
        },
        mounted() {
            Event.$on('guardar', this.guardar);
            Event.$on('editar', () => { this.edicion = true; });
            Event.$on('clonar', this.clonar);
            Event.$on('eliminar', this.eliminar);

            if(this.id) {
                axios.get('/admin/ajax/actividades/' + this.id)
                    .then((datos) => {
                        this.actividad = datos.data;
                        this.normalizarMetodosPago();
                        if (this.actividad.ficha_medica_campos)
                            this.fichaMedicaCampos = this.actividad.ficha_medica_campos;
                        else
                            this.fichaMedicaCampos =   {
                                'contacto_emergencia' : false,
                                'grupo_sanguinieo' : false,
                                'cobertura_medica': false,
                                'ficha_alergias' : false,
                                'ficha_alimentacion' : false,
                                'documento_identidad' : false,
                                'vacunacion_covid' : false,
                                'enfermedades_preexistentes' : false
                            };
                        if (this.actividad.roles_tags)
                            this.rolesTags = this.rolesFallback.filter(role =>
                                    this.actividad.roles_tags.includes(role.id)
                            );

                        if (this.actividad.actividades_tags)
                            this.actividadesTags = this.actividad.actividades_tags;

                        
                        if (this.actividad.tipo_inscriptos_tag)
                            this.tipoInscriptosTags = this.tipoVoluntarioFallback .filter(tipo =>
                                    this.actividad.tipo_inscriptos_tag.includes(tipo.id)
                            );
                        
                        if (this.actividad.comunidades) {
                            this.comunidadesTags = this.actividad.comunidades.map(comunidad => {
                                return {
                                    idComunidad: comunidad.idComunidad,
                                    text: comunidad.nombre,
                                    tiClasses: ['ti-valid']
                                };
                            });
                        }

                        this.getTodasRelaciones();
                        this.cargarFechas();
                    }).catch((error) => { debugger; });
            }
            else {
                this.getRelaciones();
            }


        },
        computed: {
            // Deriva el modo activo a partir de los dos booleanos pago + confirmacion.
            // automatica: 0/0 · donacion: pago=1/conf=0 · manual: pago=0/conf=1 · mixto: 1/1
            modoActual() {
                const pago = this.actividad.pago == 1;
                const conf = this.actividad.confirmacion == 1;
                if (pago && conf) return 'mixto';
                if (pago) return 'donacion';
                if (conf) return 'manual';
                return 'automatica';
            },
            modosConfirmacion() {
                const t = (k) => this.$t('backend.' + k);
                return [
                    {
                        key: 'automatica',
                        icon: '⚡',
                        title: t('automatic'),
                        desc: t('mode_automatic_desc'),
                    },
                    {
                        key: 'donacion',
                        icon: '💵',
                        title: t('mode_donation_title'),
                        desc: t('mode_donation_desc'),
                    },
                    {
                        key: 'manual',
                        icon: '🔧',
                        title: t('manual'),
                        desc: t('mode_manual_desc'),
                    },
                    {
                        key: 'mixto',
                        icon: '🔁',
                        title: t('mode_mixed_title'),
                        desc: t('mode_mixed_desc'),
                    },
                ];
            },
            tipoVoluntarioFallback() {
                const roles =
                    this.$i18n.messages[this.$i18n.locale]
                        .backend
                        .tipo_voluntariado_options;

                return Object.entries(roles).map(([id, text]) => ({
                    id,
                    text
                }));
            },

            filteredTipoInscriptosTags() {
                if (!this.tag) return this.tipoVoluntarioFallback;

                const search = this.tag.toLowerCase();

                return this.tipoVoluntarioFallback.filter(tipo =>
                    tipo.text.toLowerCase().includes(search)
                    );
            },
            rolesFallback() {
                const roles =
                    this.$i18n.messages[this.$i18n.locale]
                        .backend
                        .roles_actividad_options;

                return Object.entries(roles).map(([id, text]) => ({
                    id,
                    text
                }));
            },

            filteredRolesTags() {
                if (!this.tag) return this.rolesFallback;

                const search = this.tag.toLowerCase();

                return this.rolesFallback.filter(role =>
                    role.text.toLowerCase().includes(search)
                    );
            },
            filteredActividadTags() {
                return this.autocompleteActividadTags.filter(i => {
                    return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
                });
            },

            filteredComunidadTags() {
                return this.autocompleteComunidadesTags.filter(i => {
                    return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
                });
            },

            errorFinInscripcionesPostInicio() {
                if (!this.fechas.fechaFinInscripciones || !this.fechas.fechaInicio) return false;
                return moment(this.fechas.fechaFinInscripciones).isAfter(moment(this.fechas.fechaInicio), 'day');
            },

            statusInscripcionesChip() {
                return this._periodChip(
                    this.fechas.fechaInicioInscripciones, this.horas.fechaInicioInscripciones,
                    this.fechas.fechaFinInscripciones,   this.horas.fechaFinInscripciones,
                    'registrations'
                );
            },
            statusActividadChip() {
                return this._periodChip(
                    this.fechas.fechaInicio, this.horas.fechaInicio,
                    this.fechas.fechaFin,    this.horas.fechaFin,
                    'activity'
                );
            },
            statusEvaluacionesChip() {
                return this._periodChip(
                    this.fechas.fechaInicioEvaluaciones, this.horas.fechaInicioEvaluaciones,
                    this.fechas.fechaFinEvaluaciones,    this.horas.fechaFinEvaluaciones,
                    'evaluations'
                );
            },
        },
        filters: {},
        watch: {
            'fechas.fechaInicio': {
                handler(){
                    this.calcularFechas();
                }
            },
            'fechas.fechaFin': {
                handler(){
                    this.calcularFechas();
                }
            },
            'horas.fechaInicio': {
                handler(){
                    this.calcularFechas();
                }
            },
            'horas.fechaFin': {
                handler(){
                    this.calcularFechas();
                }
            },
        },
        methods: {
            cargarFechas(){
                // Detectar si las inscripciones fueron modificadas manualmente:
                // comparamos AMBOS extremos (inicio y fin) contra lo que generaría
                // el cálculo automático. Si cualquiera difiere, fueron editadas a mano.
                if (this.actividad.fechaInicio && this.actividad.fechaInicioInscripciones && this.actividad.fechaFinInscripciones) {
                    const autoInicioInsc = moment(this.actividad.fechaInicio).subtract(10, 'd');
                    const autoFinInsc    = moment(this.actividad.fechaInicio);
                    this.modoManualInscripciones =
                        !moment(this.actividad.fechaInicioInscripciones).isSame(autoInicioInsc, 'minute') ||
                        !moment(this.actividad.fechaFinInscripciones).isSame(autoFinInsc, 'minute');
                } else {
                    this.modoManualInscripciones = false;
                }

                // Detectar si las evaluaciones fueron modificadas manualmente
                if (this.actividad.fechaFin && this.actividad.fechaInicioEvaluaciones && this.actividad.fechaFinEvaluaciones) {
                    const autoInicioEval = moment(this.actividad.fechaFin).add(1, 'd');
                    const autoFinEval    = moment(this.actividad.fechaFin).add(30, 'd');
                    this.modoManualEvaluaciones =
                        !moment(this.actividad.fechaInicioEvaluaciones).isSame(autoInicioEval, 'minute') ||
                        !moment(this.actividad.fechaFinEvaluaciones).isSame(autoFinEval, 'minute');
                } else {
                    this.modoManualEvaluaciones = false;
                }

                this.fechas.fechaInicio = moment(this.actividad.fechaInicio).format('YYYY-MM-DD');
                this.horas.fechaInicio = moment(this.actividad.fechaInicio).format('HH:mm:ss');
                this.fechas.fechaFin = moment(this.actividad.fechaFin).format('YYYY-MM-DD');
                this.horas.fechaFin = moment(this.actividad.fechaFin).format('HH:mm:ss');

                this.fechas.fechaInicioInscripciones = moment(this.actividad.fechaInicioInscripciones).format('YYYY-MM-DD');
                this.horas.fechaInicioInscripciones = moment(this.actividad.fechaInicioInscripciones).format('HH:mm:ss');

                this.fechas.fechaFinInscripciones = moment(this.actividad.fechaFinInscripciones).format('YYYY-MM-DD');
                this.horas.fechaFinInscripciones = moment(this.actividad.fechaFinInscripciones).format('HH:mm:ss');

                this.fechas.fechaInicioEvaluaciones = moment(this.actividad.fechaInicioEvaluaciones).format('YYYY-MM-DD');
                this.horas.fechaInicioEvaluaciones = moment(this.actividad.fechaInicioEvaluaciones).format('HH:mm:ss');

                this.fechas.fechaFinEvaluaciones = moment(this.actividad.fechaFinEvaluaciones).format('YYYY-MM-DD');
                this.horas.fechaFinEvaluaciones = moment(this.actividad.fechaFinEvaluaciones).format('HH:mm:ss');

                this.fechas.fechaLimitePago = moment(this.actividad.fechaLimitePago).format('YYYY-MM-DD');
                this.horas.fechaLimitePago = moment(this.actividad.fechaLimitePago).format('HH:mm:ss');
            },
            calcularFechas(){
                if (!this.modoManualInscripciones && this.fechas.fechaInicio) {
                    this.fechas.fechaInicioInscripciones = moment(this.fechas.fechaInicio).subtract(10, 'd').format('YYYY-MM-DD');
                    this.fechas.fechaFinInscripciones = this.fechas.fechaInicio;
                    this.horas.fechaInicioInscripciones = this.horas.fechaInicio;
                    this.horas.fechaFinInscripciones = this.horas.fechaInicio;
                }

                if (!this.modoManualEvaluaciones && this.fechas.fechaFin) {
                    this.fechas.fechaInicioEvaluaciones = moment(this.fechas.fechaFin).add(1, 'd').format('YYYY-MM-DD');
                    this.fechas.fechaFinEvaluaciones = moment(this.fechas.fechaFin).add(30, 'd').format('YYYY-MM-DD');
                    this.horas.fechaInicioEvaluaciones = this.horas.fechaFin;
                    this.horas.fechaFinEvaluaciones = this.horas.fechaFin;
                }

                if (!this.actividad.pago)
                    this.fechas.fechaLimitePago = moment(this.fechas.fechaFin).format('YYYY-MM-DD');

                this.estadoInscripcion = moment().isBetween(
                    this.fechas.fechaInicioInscripciones + ' ' + this.horas.fechaInicioInscripciones,
                    this.fechas.fechaFinInscripciones + ' ' + this.horas.fechaFinInscripciones
                );

                this.estadoEvaluaciones = moment().isBetween(
                    this.fechas.fechaInicioEvaluaciones + ' ' + this.horas.fechaInicioEvaluaciones,
                    this.fechas.fechaFinEvaluaciones + ' ' + this.horas.fechaFinEvaluaciones
                );

                this.estadoPago = moment().isBefore(this.fechas.fechaLimitePago);
            },
            restaurarInscripcionesAuto(){
                this.modoManualInscripciones = false;
                this.calcularFechas();
            },
            restaurarEvaluacionesAuto(){
                this.modoManualEvaluaciones = false;
                this.calcularFechas();
            },
            _periodChip(startDate, startTime, endDate, endTime, kind) {
                if (!startDate || !endDate) return null;
                const now = moment();
                const inicio = moment(startDate + ' ' + (startTime || '00:00:00'));
                const fin    = moment(endDate   + ' ' + (endTime   || '23:59:59'));
                const daysToStart = inicio.diff(now, 'days');
                const daysToEnd   = fin.diff(now, 'days');
                const t = (k, p) => this.$t('backend.' + k, p);

                if (now.isBefore(inicio)) {
                    const d = Math.max(0, daysToStart);
                    const label = d === 0 ? t(kind === 'activity' ? 'status_starts_today'    : 'status_opens_today')
                                : d === 1 ? t(kind === 'activity' ? 'status_starts_tomorrow' : 'status_opens_tomorrow')
                                :           t(kind === 'activity' ? 'status_starts_in_days'  : 'status_opens_in_days', { n: d });
                    return { label, variant: d <= 2 ? 'warning' : 'upcoming' };
                }

                if (now.isBefore(fin)) {
                    const d = Math.max(0, daysToEnd);
                    let label;
                    if (kind === 'activity') {
                        label = d === 0 ? t('status_in_progress_ends_today')
                              : d === 1 ? t('status_in_progress_ends_tomorrow')
                              : d <= 7  ? t('status_in_progress_ends_in_days', { n: d })
                              :           t('status_in_progress');
                    } else {
                        label = d === 0 ? t('status_closes_today')
                              : d === 1 ? t('status_closes_tomorrow')
                              :           t('status_closes_in_days', { n: d });
                    }
                    const variant = d === 0 ? 'danger' : d <= 3 ? 'warning' : 'active';
                    return { label, variant };
                }

                const label = kind === 'activity' ? t('status_finished') : t('status_closed');
                return { label, variant: 'closed' };
            },
            // Setea pago + confirmacion según la tarjeta de modo elegida.
            seleccionarModo(modo){
                if (!this.edicion) return;
                switch (modo) {
                    case 'automatica': this.actividad.pago = 0; this.actividad.confirmacion = 0; break;
                    case 'donacion':   this.actividad.pago = 1; this.actividad.confirmacion = 0; break;
                    case 'manual':     this.actividad.pago = 0; this.actividad.confirmacion = 1; break;
                    case 'mixto':      this.actividad.pago = 1; this.actividad.confirmacion = 1; break;
                }
                this.normalizarMetodosPago();
            },
            // metodos_pago viene como null/objeto parcial desde la BD; garantiza las 3 claves
            // y que sea reactivo (Vue.set no hace falta al reemplazar el objeto entero).
            normalizarMetodosPago(){
                const mp = this.actividad.metodos_pago || {};
                this.actividad.metodos_pago = {
                    transferencia: !!mp.transferencia,
                    link_pix: !!mp.link_pix,
                    tarjeta: !!mp.tarjeta,
                };
                this.actividad.permite_exencion = !!this.actividad.permite_exencion;
            },
            guardar(){
                if (this.errorFinInscripcionesPostInicio) return;

                this.actividad.fechaInicio = moment(this.fechas.fechaInicio + ' ' + this.horas.fechaInicio).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaFin = moment(this.fechas.fechaFin + ' ' + this.horas.fechaFin).format('YYYY-MM-DD HH:mm:ss');
                
                if (this.virtual){
                    this.actividad.idProvincia = 44;
                    this.actividad.idLocalidad = 2663;
                }
                this.actividad.fechaInicioInscripciones = moment(this.fechas.fechaInicioInscripciones + ' ' + this.horas.fechaInicioInscripciones).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaFinInscripciones = moment(this.fechas.fechaFinInscripciones + ' ' + this.horas.fechaFinInscripciones).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaInicioEvaluaciones = moment(this.fechas.fechaInicioEvaluaciones + ' ' + this.horas.fechaInicioEvaluaciones).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaFinEvaluaciones = moment(this.fechas.fechaFinEvaluaciones + ' ' + this.horas.fechaFinEvaluaciones).format('YYYY-MM-DD HH:mm:ss');
                
                if (this.actividad.pago==1){
                    this.actividad.fechaLimitePago = this.fechas.fechaLimitePago;
                }
                
                this.actividad.ficha_medica_campos = this.fichaMedicaCampos;
                if(this.rolesTags.length > 0)
                    this.actividad.roles_tags = this.rolesTags.map(tag => tag.id);
                else   
                    this.actividad.roles_tags = [];
                
                if(this.tipoInscriptosTags.length > 0)
                    this.actividad.tipo_inscriptos_tag = this.tipoInscriptosTags.map(tag => tag.id);
                else
                    this.actividad.tipo_inscriptos_tag = [];

                this.actividad.actividades_tags  = this.actividadesTags;
                this.actividad.comunidades_tags  = this.comunidadesTags ;
                
                

                if(this.id) {
                    axios.post('/admin/ajax/actividades/' + this.id, this.actividad)
                        .then((datos) => { 
                            this.guardarImagenTarjeta();
                            this.guardarImagenDestacada();
                            this.actividad = datos.data;
                            this.normalizarMetodosPago();
                            Event.$emit('success');
                            this.edicion = false;
                            this.reset_errors();

                        })
                        .catch((error) => { 
                            this.errors = error.response.data.errors;
                            Event.$emit('error');
                        });
                }
                else {
                    this.errors = {};
                    axios.post('/admin/actividades/crear', this.actividad)
                        .then((datos) => { window.location = '/admin/actividades/' + datos.data.idActividad; })
                        .catch((error) => {
                            this.errors = error.response.data.errors;
                            Event.$emit('error');
                        });
                }
            },
            reset_errors: function () {
                for (let field in this.errors) {
                    this.errors[field] = null;
                    delete this.errors[field];
                }
            },
            updatePhoto: function ({ blob, imageUrl }) {

                if (this.actividad.imagenNew) {
                    URL.revokeObjectURL(this.actividad.imagenNew);
                }
                this.croppedImagenTarjetaUrl = imageUrl;
                this.actividad.imagenNew = blob;
                this.openPhotoEdit = false;
            },
            selectPhoto: function () {
                this.openPhotoEdit = !this.openPhotoEdit;
            },
            updatePhotoDestacada: function ({ blob, imageUrl }) {
                
                if (this.actividad.imagenDestacadaNew) {
                    URL.revokeObjectURL(this.actividad.imagenDestacadaNew);
                }
                this.croppedImagenDestacadaUrl = imageUrl;
                this.actividad.imagenDestacadaNew = blob;
                this.openPhotoEditDestacada = false;
                },
            selectPhotoDestacada: function () {
                this.openPhotoEditDestacada = !this.openPhotoEditDestacada;
            },

            guardarImagenTarjeta(){
                let url = `/admin/ajax/actividades/${encodeURI(this.actividad.idActividad)}/imagen-tarjeta`;
                
                const data = new FormData();

                if (this.actividad.imagenNew != null){
                    const file = new File([this.actividad.imagenNew], 'cropped-image-'+this.actividad.idActividad+'.jpg', { type: 'image/png' });

                    data.append('imagen_tarjeta', file);

                    const headers = { 'Content-Type': 'multipart/form-data' };
                    axios.post(url, data, { headers })
                        .then((respuesta) => {
                            this.actividad.imagen_tarjeta = respuesta.data;
                        })
                        .catch((error) => { 
                            
                            this.errors = error;
                        });
                }
            },
            guardar_archivo(event) {
                this.actividad.imagenNew = this.$refs.imagen_tarjeta.files[0];
            },
            guardarImagenDestacada(){
                let url = `/admin/ajax/actividades/${encodeURI(this.actividad.idActividad)}/imagen-destacada`;
                
                const data = new FormData();

                if (this.actividad.imagenDestacadaNew != null){
                    const file = new File([this.actividad.imagenDestacadaNew], 'cropped-image-'+this.actividad.idActividad+'.jpg', { type: 'image/png' });

                    data.append('imagen_destacada', file);


                    const headers = { 'Content-Type': 'multipart/form-data' };
                    axios.post(url, data, { headers })
                        .then((respuesta) => {
                            this.actividad.imagen_destacada = respuesta.data;
                        })
                        .catch((error) => { 
                           
                            this.errors = error.errors;
                        });
                }
            },
            guardar_destacada(event) {
                this.actividad.imagenDestacada = this.$refs.imagen_destacada.files[0];
            },
            getRelaciones(){
                this.getPaises();                
                this.getTipos(1);
                this.getCategorias();
            },
            getTodasRelaciones(){
                this.getPaises();
                this.getProvincias();
                this.getLocalidades();
                this.getOficinas();
                this.getEquipos();
                this.getTipos(this.actividad.tipo.idCategoria);
                this.getCategorias();
                this.getComunidades(this.actividad.idOficina);
            },
            getPaises(){
                axios.get('/ajax/paises/propios')
                    .then((datos) => { this.paises = datos.data; }).catch((error) => { debugger; });
            },
            getProvincias(){
                axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias')
                    .then((datos) => { this.provincias = datos.data; }).catch((error) => { debugger; });
            },
            getLocalidades(){
                axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias/' + this.actividad.idProvincia + '/localidades')
                    .then((datos) => { this.localidades = datos.data; }).catch((error) => { debugger; });
            },
            getOficinas(){
                axios.get('/admin/ajax/oficinas/pais/' + this.actividad.idPais)
                    .then((datos) => { this.oficinas = datos.data; }).catch((error) => { debugger; });
            },
            getEquipos(){
                const params = this.actividad.idActividad ? { idActividad: this.actividad.idActividad } : {};
                axios.get('/admin/ajax/equipos/oficina/' + this.actividad.idOficina, { params })
                    .then((datos) => { this.equipos = datos.data.data; }).catch((error) => { debugger; });
            },
            getTipos(id){
                this.idCategoria = id;
                axios.get('/ajax/categorias/' + id + '/tipos/activas')
                    .then((datos) => { 
                        this.tipos = datos.data; 
                    }).catch((error) => { debugger; });
                if (id == 4) { this.virtual = true; } else { this.virtual = false; }
            },
            getCategorias(){
                axios.get('/ajax/categorias/')
                    .then((datos) => { this.categorias = datos.data; }).catch((error) => { debugger; });
            },
            getComunidades(idOficina){
                axios.get('/ajax/comunidades/' + idOficina )
                    .then((datos) => { 
                        this.autocompleteComunidadesTags = this.formatComunidades(datos.data);
                    }).catch((error) => { debugger; });
            },
            formatComunidades(response) {
                return response.map(comunidad => ({
                    text: comunidad.nombre,
                    idComunidad: comunidad.idComunidad
                }));
            },
            tiny_mce_filemanager_callback(callback, value, meta) {
                //gracias a esto ❤ https://github.com/UniSharp/laravel-filemanager/issues/759 
                let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                let y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
                let cmsURL = '/laravel-filemanager?editor=tinymce5&field_name=' + value;
                if (meta.filetype == 'image') { cmsURL = cmsURL + "&type=Images"; } 
                else { cmsURL = cmsURL + "&type=Files"; }

                tinyMCE.activeEditor.windowManager.openUrl({
                    url : cmsURL,
                    title : 'Administrador de archivos',
                    width : x * 0.8,
                    height : y * 0.8,
                    resizable : "yes",
                    close_previous : "no",
                    onMessage: (api, message) => {
                        callback(message.content);
                    }
                });
            },
            cargarAuditoria: function(id) {
                Event.$emit('cargarAuditoria', {tabla: 'actividad', id: id});
            },
            clonar: function() {
                let url = '/admin/ajax/actividades/'+ this.actividad.idActividad +'/clonar';
                let params = { idActividad: this.actividad.idActividad };
                debugger;
                this.axiosPost(url, function(response, self) {
                    if (response.idActividad) {
                        window.location = '/admin/actividades/' + response.idActividad
                    }
                }, params,
                    function (response, self) {
                    // Si hay error
                        this.errors = error.response.data.errors;
                        Event.$emit('error');
                    })
            },
            eliminar: function () {
                let form = document.getElementById('formDelete');
                form.submit();
            },
        }
    }
</script>

<style scoped>
/* ── Dates section ─────────────────────────────────────── */
.dates-section-header {
    margin: 20px -10px 16px;
    border-radius: 0;
}

.dates-cards-row {
    margin-bottom: 16px;
}

/* Cards */
.dates-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    height: 100%;
    overflow: hidden;
}

.dates-card-header {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 10px 14px;
    border-bottom: 1px solid #eee;
    flex-wrap: wrap;
}

.dates-card-header--activity {
    border-left: 4px solid #3c8dbc;
}
.dates-card-header--registrations {
    border-left: 4px solid #e67e22;
}
.dates-card-header--evaluations {
    border-left: 4px solid #f1c40f;
}

.dates-card-icon {
    font-size: 16px;
    line-height: 1;
}

.dates-card-title {
    font-weight: 600;
    font-size: 13px;
    color: #444;
    margin: 0;
}
.dates-card-header--activity .dates-card-title  { color: #2c6f99; }
.dates-card-header--registrations .dates-card-title { color: #c0621a; }
.dates-card-header--evaluations .dates-card-title { color: #b8940a; }

.dates-card-title--checkbox {
    font-weight: 600;
    cursor: pointer;
}

.dates-card-body {
    padding: 12px 14px;
}

.dates-show-checkbox {
    margin-bottom: 10px;
    font-size: 13px;
}

/* Badges */
.dates-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.dates-badge--auto {
    background: #d6eaf8;
    color: #1a6fa3;
}
.dates-badge--manual {
    background: #fde8d8;
    color: #b94a08;
}

/* Toggle link */
.dates-toggle {
    margin-bottom: 8px;
}
.dates-toggle-btn {
    padding: 0;
    font-size: 12px;
    color: #3c8dbc;
    text-decoration: none;
    border: none;
    background: none;
}
.dates-toggle-btn:hover {
    color: #2c6f99;
    text-decoration: underline;
}

/* Hint */
.dates-hint {
    font-size: 11px;
    color: #999;
    margin-top: 4px;
}

.dates-card-divider {
    margin: 12px 0;
    border-color: #f0f0f0;
}

/* Validation */
.dates-card--error {
    border-color: #e74c3c;
    box-shadow: 0 1px 4px rgba(231, 76, 60, 0.2);
}
.dates-card--error .dates-card-header--registrations {
    border-left-color: #e74c3c;
    background: #fdf0ef;
}
.dates-validation-error {
    color: #c0392b;
    font-size: 12px;
    font-weight: 500;
    margin-top: 4px;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.dates-validation-error::before {
    content: '⚠';
    font-size: 13px;
}
.dates-card-error-icon {
    margin-left: auto;
    font-size: 14px;
}

/* Status chips */
.dates-status {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 10px;
    letter-spacing: 0.2px;
}
.dates-status--upcoming { background: #d6eaf8; color: #1a6fa3; }
.dates-status--active   { background: #d5f5e3; color: #1a7a3c; }
.dates-status--warning  { background: #fef9e7; color: #9a6e00; border: 1px solid #f5d76e; }
.dates-status--danger   { background: #fde8d8; color: #b94a08; border: 1px solid #f5b09a; }
.dates-status--closed   { background: #f0f0f0; color: #999; }
.dates-status--finished { background: #f0f0f0; color: #999; }

/* ── Confirmación y Pago: selector de modo ─────────────── */
.modo-cards {
    display: flex;
    flex-wrap: wrap;
    margin-top: 8px;
    margin-bottom: 8px;
}
/* Cada columna como flex-container para que la tarjeta estire a la altura de la fila */
.modo-cards > [class*="col-"] {
    display: flex;
}
.modo-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s, background .15s;
    height: calc(100% - 16px);
}
.modo-card:hover {
    border-color: #b8d4e6;
    box-shadow: 0 1px 6px rgba(0,0,0,0.08);
}
.modo-card--active {
    border-color: #3c8dbc;
    box-shadow: 0 0 0 2px rgba(60,141,188,0.35);
    background: #f4f9fc;
}
.modo-card--disabled {
    cursor: not-allowed;
    opacity: 0.75;
}
.modo-card__icon {
    font-size: 22px;
    line-height: 1.2;
}
.modo-card__body strong {
    display: block;
    font-size: 14px;
    color: #333;
    margin-bottom: 3px;
}
.modo-card__body p {
    margin: 0;
    font-size: 12px;
    color: #777;
    line-height: 1.4;
}

/* ── Sección de pago ───────────────────────────────────── */
.pago-section-title {
    font-size: 13px;
    font-weight: 700;
    color: #555;
    text-transform: none;
    border-bottom: 1px solid #eee;
    padding-bottom: 6px;
    margin: 18px 0 14px;
}

/* Caja naranja de exención */
.exencion-box {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
    font-weight: normal;
    margin: 24px 0 0;
    padding: 12px 14px;
    border: 1px solid #f0d9b5;
    border-radius: 8px;
    background: #fdf6ec;
    cursor: pointer;
}
.exencion-box--active {
    border-color: #e6a23c;
    background: #fbefd8;
}
.exencion-box--disabled {
    cursor: not-allowed;
    opacity: 0.75;
}
.exencion-box input {
    margin-top: 3px;
}
.exencion-box strong {
    display: block;
    font-size: 13px;
    color: #8a5a00;
}
.exencion-box small {
    display: block;
    font-size: 11px;
    color: #a07840;
    line-height: 1.4;
    margin-top: 2px;
}

/* Tarjetas de método de pago */
.metodos-row {
    margin-bottom: 6px;
}
.metodo-card {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
    font-weight: normal;
    margin: 0 0 12px;
    padding: 12px 14px;
    border: 1px solid #dcd6ec;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    height: calc(100% - 12px);
}
.metodo-card:hover {
    border-color: #b9a9e0;
}
.metodo-card--active {
    border-color: #7e57c2;
    box-shadow: 0 0 0 2px rgba(126,87,194,0.25);
}
.metodo-card--disabled {
    cursor: not-allowed;
    opacity: 0.75;
}
.metodo-card input {
    margin-top: 3px;
}
.metodo-card strong {
    display: block;
    font-size: 13px;
    color: #333;
}
.metodo-card small {
    display: block;
    font-size: 11px;
    color: #888;
    line-height: 1.4;
    margin-top: 2px;
}
</style>
