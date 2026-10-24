<x-layouts.public>
 <section class="portal-hero portal-container" aria-labelledby="portal-title">
  <div class="portal-hero-copy">
   <p class="portal-eyebrow"><span></span>{{ config('portal.eyebrow') }}</p>
   <h1 id="portal-title">{{ config('portal.title') }}</h1>
   <p class="portal-intro">{{ config('portal.description') }}</p>
   <div class="portal-hero-actions">
    <a class="portal-button" href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ auth()->check() ? 'Ir a mi panel' : 'Acceder a la plataforma' }}<x-ui.icon name="arrow-right" /></a>
    <a class="portal-text-link" href="#capacidades">Conoce la plataforma <span aria-hidden="true">↘</span></a>
   </div>
   <div class="portal-assurance"><x-ui.icon name="shield" /><span>Acceso por roles y permisos.</span></div>
  </div>
  @include('public.partials.operation-map')
 </section>
 <section id="capacidades" class="portal-capabilities" aria-labelledby="capabilities-title">
  <div class="portal-container">
   <div class="portal-section-heading"><div><p class="portal-eyebrow">MENOS DISPERSIÓN. MÁS CONTEXTO.</p><h2 id="capabilities-title">La información acompaña<br>cada etapa del servicio.</h2></div><p>Consulta el contexto del equipo, coordina la atención y conserva el registro del trabajo realizado.</p></div>
   <div class="portal-capability-grid">
    @foreach(config('portal.capabilities') as $capability)
     <article class="portal-capability"><div class="portal-card-top"><span class="portal-card-icon"><x-ui.icon :name="$capability['icon']" /></span><span>{{ $capability['number'] }}</span></div><h3>{{ $capability['title'] }}</h3><p>{{ $capability['text'] }}</p></article>
    @endforeach
   </div>
  </div>
 </section>
 <section id="flujo" class="portal-workflow portal-container" aria-labelledby="workflow-title">
  <div class="portal-section-heading"><div><p class="portal-eyebrow">UNA SECUENCIA CLARA</p><h2 id="workflow-title">Del equipo al informe.</h2></div><p>Conserva el vínculo entre la solicitud, la intervención y su documentación.</p></div>
  <ol class="portal-steps">
   <li><span>01</span><div><h3>Identifica</h3><p>Consulta el equipo, su cliente e institución.</p></div></li>
   <li><span>02</span><div><h3>Coordina</h3><p>Revisa el ticket, la prioridad y el responsable.</p></div></li>
   <li><span>03</span><div><h3>Registra</h3><p>Relaciona servicios, actividades e informes.</p></div></li>
  </ol>
 </section>
 <section class="portal-cta portal-container"><div><p class="portal-eyebrow">CONTINÚA CON TU TRABAJO</p><h2>Tu próxima intervención<br>empieza aquí.</h2></div><a class="portal-button" href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ auth()->check() ? 'Ir al panel' : 'Iniciar sesión' }}<x-ui.icon name="arrow-right" /></a></section>
</x-layouts.public>
